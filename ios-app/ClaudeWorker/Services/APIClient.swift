import Foundation

enum APIError: LocalizedError {
    case invalidURL
    case notAuthenticated
    case serverError(String)
    case networkError(Error)

    var errorDescription: String? {
        switch self {
        case .invalidURL: return "Invalid server URL"
        case .notAuthenticated: return "Not authenticated. Please log in."
        case .serverError(let msg): return msg
        case .networkError(let err): return "Network error: \(err.localizedDescription)"
        }
    }
}

struct LoginResponse: Decodable {
    let authenticated: Bool?
    let token: String?
    let error: String?
}

@MainActor
class APIClient: ObservableObject {
    @Published var isAuthenticated = false
    @Published var serverURL = ""

    private var authToken: String?
    private let session: URLSession

    static let shared = APIClient()

    init() {
        let config = URLSessionConfiguration.default
        config.timeoutIntervalForRequest = 30
        self.session = URLSession(configuration: config)

        let settings = ServerSettings.load()
        self.serverURL = settings.serverURL

        // Restore token
        if let token = UserDefaults.standard.string(forKey: "cw_auth_token"), !token.isEmpty {
            self.authToken = token
            self.isAuthenticated = true
        }
    }

    private func baseURL() throws -> URL {
        let urlString = serverURL.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !urlString.isEmpty, let url = URL(string: urlString) else {
            throw APIError.invalidURL
        }
        return url
    }

    // MARK: - Authentication

    func login(password: String) async throws {
        let url = try baseURL().appendingPathComponent("claude-worker/login")
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.httpBody = try JSONEncoder().encode(["password": password])

        let (data, response) = try await session.data(for: request)

        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError(URLError(.badServerResponse))
        }

        if httpResponse.statusCode == 401 || httpResponse.statusCode == 422 {
            if let loginResp = try? JSONDecoder().decode(LoginResponse.self, from: data), let err = loginResp.error {
                throw APIError.serverError(err)
            }
            throw APIError.serverError("Invalid password")
        }

        if httpResponse.statusCode >= 400 {
            let bodyStr = String(data: data, encoding: .utf8) ?? "Unknown error"
            throw APIError.serverError("HTTP \(httpResponse.statusCode): \(String(bodyStr.prefix(200)))")
        }

        // Parse token from response
        if let loginResp = try? JSONDecoder().decode(LoginResponse.self, from: data), let token = loginResp.token {
            authToken = token
            UserDefaults.standard.set(token, forKey: "cw_auth_token")
            isAuthenticated = true
            return
        }

        // Fallback: use password as bearer token directly
        authToken = password
        UserDefaults.standard.set(password, forKey: "cw_auth_token")
        isAuthenticated = true
    }

    func logout() {
        authToken = nil
        UserDefaults.standard.removeObject(forKey: "cw_auth_token")
        isAuthenticated = false
    }

    // MARK: - Private Helpers

    private func apiRequest(_ method: String, path: String, body: [String: Any]? = nil) async throws -> Data {
        let url = try baseURL().appendingPathComponent(path)
        var request = URLRequest(url: url)
        request.httpMethod = method
        request.setValue("application/json", forHTTPHeaderField: "Accept")

        // Bearer token auth
        if let token = authToken {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }

        if let body = body {
            request.setValue("application/json", forHTTPHeaderField: "Content-Type")
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
        }

        do {
            let (data, response) = try await session.data(for: request)

            if let httpResponse = response as? HTTPURLResponse {
                if httpResponse.statusCode == 403 {
                    isAuthenticated = false
                    authToken = nil
                    UserDefaults.standard.removeObject(forKey: "cw_auth_token")
                    throw APIError.notAuthenticated
                }
                if httpResponse.statusCode >= 400 {
                    if let errorResp = try? JSONDecoder().decode(ErrorResponse.self, from: data) {
                        let msg = errorResp.error ?? errorResp.errors?.values.flatMap({ $0 }).joined(separator: "\n") ?? "Unknown error"
                        throw APIError.serverError(msg)
                    }
                    let bodyStr = String(data: data, encoding: .utf8) ?? "Unknown error"
                    throw APIError.serverError("HTTP \(httpResponse.statusCode): \(String(bodyStr.prefix(200)))")
                }
            }

            return data
        } catch let error as APIError {
            throw error
        } catch {
            throw APIError.networkError(error)
        }
    }

    // MARK: - Tasks

    func launchTask(
        apiKey: String,
        task: String,
        repoSource: String,
        repoURL: String?,
        localRepo: String?,
        branch: String? = nil,
        repoBranch: String? = nil,
        model: String? = nil,
        maxTurns: Int? = nil,
        push: Bool = false,
        rebuild: Bool = false,
        verbose: Bool = false
    ) async throws -> LaunchTaskResponse {
        var body: [String: Any] = [
            "api_key": apiKey,
            "task": task,
            "repo_source": repoSource,
            "push": push,
            "rebuild": rebuild,
            "verbose": verbose
        ]

        if repoSource == "url", let url = repoURL, !url.isEmpty {
            body["repo_url"] = url
        }
        if repoSource == "local", let path = localRepo, !path.isEmpty {
            body["local_repo"] = path
        }
        if let b = branch, !b.isEmpty { body["branch"] = b }
        if let rb = repoBranch, !rb.isEmpty { body["repo_branch"] = rb }
        if let m = model, !m.isEmpty { body["model"] = m }
        if let mt = maxTurns { body["max_turns"] = mt }

        let data = try await apiRequest("POST", path: "claude-worker/launch", body: body)
        return try JSONDecoder().decode(LaunchTaskResponse.self, from: data)
    }

    func fetchTasks() async throws -> [WorkerTask] {
        let data = try await apiRequest("GET", path: "claude-worker/tasks")
        let response = try JSONDecoder().decode(TasksResponse.self, from: data)
        return response.tasks
    }

    func fetchTaskLogs(taskId: String) async throws -> TaskLogsResponse {
        let data = try await apiRequest("GET", path: "claude-worker/tasks/\(taskId)/logs")
        return try JSONDecoder().decode(TaskLogsResponse.self, from: data)
    }

    func stopTask(taskId: String) async throws {
        _ = try await apiRequest("POST", path: "claude-worker/tasks/\(taskId)/stop")
    }

    // MARK: - Runs

    func fetchRuns() async throws -> [CompletedRun] {
        let data = try await apiRequest("GET", path: "claude-worker/runs")
        let response = try JSONDecoder().decode(RunsResponse.self, from: data)
        return response.runs
    }

    func fetchRunDiff(runId: String) async throws -> String {
        let data = try await apiRequest("GET", path: "claude-worker/runs/\(runId)/diff")
        let response = try JSONDecoder().decode(DiffResponse.self, from: data)
        return response.diff
    }

    func fetchRunSummary(runId: String) async throws -> String {
        let data = try await apiRequest("GET", path: "claude-worker/runs/\(runId)/summary")
        let response = try JSONDecoder().decode(SummaryResponse.self, from: data)
        return response.summary
    }

    func fetchRunLogs(runId: String) async throws -> String {
        let data = try await apiRequest("GET", path: "claude-worker/runs/\(runId)/logs")
        let response = try JSONDecoder().decode(LogsResponse.self, from: data)
        return response.logs
    }

    func deleteRun(runId: String) async throws {
        _ = try await apiRequest("DELETE", path: "claude-worker/runs/\(runId)")
    }

    // MARK: - Docker

    func fetchDockerWorkers() async throws -> [DockerContainer] {
        let data = try await apiRequest("GET", path: "claude-worker/docker/workers")
        let response = try JSONDecoder().decode(DockerWorkersResponse.self, from: data)
        return response.allContainers
    }

    func fetchDockerLogs(name: String) async throws -> String {
        let data = try await apiRequest("GET", path: "claude-worker/docker/logs/\(name)")
        let response = try JSONDecoder().decode(DockerLogsResponse.self, from: data)
        return response.logs
    }

    func stopDockerContainer(name: String) async throws {
        _ = try await apiRequest("POST", path: "claude-worker/docker/stop/\(name)")
    }

    func stopAllDockerContainers() async throws {
        _ = try await apiRequest("POST", path: "claude-worker/docker/stop-all")
    }
}
