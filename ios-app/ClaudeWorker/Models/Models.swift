import Foundation

// MARK: - Authentication

struct LoginRequest: Encodable {
    let password: String
}

struct ErrorResponse: Decodable {
    let error: String?
    let errors: [String: [String]]?
}

// MARK: - Tasks

struct LaunchTaskRequest: Encodable {
    let api_key: String
    let task: String
    let repo_source: String
    let repo_url: String?
    let local_repo: String?
    let branch: String?
    let repo_branch: String?
    let model: String?
    let max_turns: Int?
    let push: Bool
    let rebuild: Bool
    let verbose: Bool
}

struct LaunchTaskResponse: Decodable {
    let task_id: String
    let pid: Int
    let status: String
}

struct WorkerTask: Decodable, Identifiable {
    let task_id: String
    let task: String
    let repo: String?
    let branch: String?
    let model: String?
    let started_at: String?
    let status: String
    let pid: String?
    let is_running: Bool?

    var id: String { task_id }

    var statusColor: String {
        switch status {
        case "running": return "blue"
        case "finished": return "green"
        case "stopped": return "orange"
        default: return "gray"
        }
    }
}

struct TasksResponse: Decodable {
    let tasks: [WorkerTask]
}

struct TaskLogsResponse: Decodable {
    let logs: String
    let is_running: Bool
}

// MARK: - Runs

struct RunInfo: Decodable {
    let task: String?
    let branch_name: String?
    let repo_url: String?
    let local_repo: String?
}

struct RunResult: Decodable {
    let status: String?
    let files_changed: Int?
}

struct CompletedRun: Decodable, Identifiable {
    let run_id: String
    let info: RunInfo?
    let result: RunResult?
    let has_diff: Bool?

    var id: String { run_id }

    var statusLabel: String {
        result?.status ?? "unknown"
    }

    var filesChanged: Int {
        result?.files_changed ?? 0
    }
}

struct RunsResponse: Decodable {
    let runs: [CompletedRun]
}

struct DiffResponse: Decodable {
    let diff: String
}

struct SummaryResponse: Decodable {
    let summary: String
}

struct LogsResponse: Decodable {
    let logs: String
}

// MARK: - Docker

struct DockerContainer: Decodable, Identifiable {
    let container_id: String?
    let name: String
    let status: String?
    let created: String?

    var id: String { name }
}

struct DockerWorkersResponse: Decodable {
    let workers: [DockerContainer]?
    let containers: [DockerContainer]?

    var allContainers: [DockerContainer] {
        workers ?? containers ?? []
    }
}

struct DockerLogsResponse: Decodable {
    let logs: String
}

// MARK: - Transcription

struct TranscribeResponse: Decodable {
    let text: String?
    let error: String?
}

// MARK: - Settings

struct ServerSettings: Codable {
    var serverURL: String
    var apiKey: String
    var repoSource: String // "url" or "local"
    var repoURL: String
    var localRepo: String
    var model: String
    var pushToRemote: Bool

    static let `default` = ServerSettings(
        serverURL: "",
        apiKey: "",
        repoSource: "url",
        repoURL: "",
        localRepo: "",
        model: "",
        pushToRemote: false
    )

    static let storageKey = "claude_worker_settings"

    func save() {
        if let data = try? JSONEncoder().encode(self) {
            UserDefaults.standard.set(data, forKey: Self.storageKey)
        }
    }

    static func load() -> ServerSettings {
        guard let data = UserDefaults.standard.data(forKey: storageKey),
              let settings = try? JSONDecoder().decode(ServerSettings.self, from: data)
        else {
            return .default
        }
        return settings
    }
}
