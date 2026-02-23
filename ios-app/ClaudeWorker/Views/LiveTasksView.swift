import SwiftUI

struct LiveTasksView: View {
    @EnvironmentObject var apiClient: APIClient
    @State private var tasks: [WorkerTask] = []
    @State private var isLoading = false
    @State private var errorMessage: String?
    @State private var selectedTaskId: String?
    @State private var autoRefreshTimer: Timer?

    var body: some View {
        NavigationStack {
            Group {
                if isLoading && tasks.isEmpty {
                    ProgressView("Loading tasks...")
                } else if tasks.isEmpty {
                    ContentUnavailableView(
                        "No Tasks Running",
                        systemImage: "bolt.slash",
                        description: Text("Launch a task from the New Task tab")
                    )
                } else {
                    taskList
                }
            }
            .navigationTitle("Live Tasks")
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    Button {
                        Task { await loadTasks() }
                    } label: {
                        Image(systemName: "arrow.clockwise")
                    }
                }
            }
            .sheet(item: Binding(
                get: { selectedTaskId.flatMap { id in tasks.first { $0.task_id == id } } },
                set: { selectedTaskId = $0?.task_id }
            )) { task in
                TaskLogView(taskId: task.task_id, taskName: task.task)
            }
            .onAppear {
                Task { await loadTasks() }
                startAutoRefresh()
            }
            .onDisappear { stopAutoRefresh() }
        }
    }

    private var taskList: some View {
        List(tasks) { task in
            VStack(alignment: .leading, spacing: 8) {
                HStack {
                    statusBadge(for: task)
                    Spacer()
                    if let started = task.started_at {
                        Text(started)
                            .font(.caption2)
                            .foregroundStyle(.secondary)
                    }
                }

                Text(task.task)
                    .font(.body)
                    .lineLimit(3)

                if let repo = task.repo, !repo.isEmpty {
                    Label(repo, systemImage: "folder")
                        .font(.caption)
                        .foregroundStyle(.secondary)
                        .lineLimit(1)
                }

                HStack(spacing: 12) {
                    Button {
                        selectedTaskId = task.task_id
                    } label: {
                        Label("Logs", systemImage: "doc.text")
                            .font(.caption.bold())
                    }
                    .buttonStyle(.bordered)
                    .tint(.indigo)

                    if task.is_running == true || task.status == "running" {
                        Button(role: .destructive) {
                            Task { await stopTask(task.task_id) }
                        } label: {
                            Label("Stop", systemImage: "stop.fill")
                                .font(.caption.bold())
                        }
                        .buttonStyle(.bordered)
                        .tint(.red)
                    }
                }
            }
            .padding(.vertical, 4)
        }
    }

    private func statusBadge(for task: WorkerTask) -> some View {
        HStack(spacing: 4) {
            Circle()
                .fill(statusColor(task.status))
                .frame(width: 8, height: 8)
            Text(task.status.capitalized)
                .font(.caption.bold())
                .foregroundStyle(statusColor(task.status))
        }
        .padding(.horizontal, 8)
        .padding(.vertical, 4)
        .background(statusColor(task.status).opacity(0.15))
        .cornerRadius(6)
    }

    private func statusColor(_ status: String) -> Color {
        switch status {
        case "running": return .blue
        case "finished": return .green
        case "stopped": return .orange
        default: return .gray
        }
    }

    private func loadTasks() async {
        if tasks.isEmpty { isLoading = true }
        do {
            tasks = try await apiClient.fetchTasks()
            errorMessage = nil
        } catch {
            errorMessage = error.localizedDescription
        }
        isLoading = false
    }

    private func stopTask(_ taskId: String) async {
        do {
            try await apiClient.stopTask(taskId: taskId)
            await loadTasks()
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    private func startAutoRefresh() {
        autoRefreshTimer = Timer.scheduledTimer(withTimeInterval: 4, repeats: true) { _ in
            Task { @MainActor in
                await loadTasks()
            }
        }
    }

    private func stopAutoRefresh() {
        autoRefreshTimer?.invalidate()
        autoRefreshTimer = nil
    }
}
