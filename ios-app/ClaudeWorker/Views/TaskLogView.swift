import SwiftUI

struct TaskLogView: View {
    @EnvironmentObject var apiClient: APIClient
    let taskId: String
    let taskName: String

    @State private var logs = ""
    @State private var isRunning = true
    @State private var isLoading = true
    @State private var pollTimer: Timer?
    @Environment(\.dismiss) private var dismiss

    var body: some View {
        NavigationStack {
            Group {
                if isLoading && logs.isEmpty {
                    ProgressView("Loading logs...")
                } else {
                    ScrollViewReader { proxy in
                        ScrollView {
                            Text(logs)
                                .font(.system(.caption, design: .monospaced))
                                .frame(maxWidth: .infinity, alignment: .leading)
                                .padding()
                                .id("logBottom")
                        }
                        .onChange(of: logs) { _, _ in
                            withAnimation {
                                proxy.scrollTo("logBottom", anchor: .bottom)
                            }
                        }
                    }
                }
            }
            .navigationTitle("Task Log")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    Button("Close") { dismiss() }
                }
                ToolbarItem(placement: .topBarTrailing) {
                    HStack(spacing: 8) {
                        if isRunning {
                            Circle()
                                .fill(.green)
                                .frame(width: 8, height: 8)
                            Text("Running")
                                .font(.caption)
                                .foregroundStyle(.green)
                        } else {
                            Circle()
                                .fill(.gray)
                                .frame(width: 8, height: 8)
                            Text("Finished")
                                .font(.caption)
                                .foregroundStyle(.secondary)
                        }
                    }
                }
            }
            .onAppear {
                Task { await fetchLogs() }
                startPolling()
            }
            .onDisappear { stopPolling() }
        }
    }

    private func fetchLogs() async {
        do {
            let response = try await apiClient.fetchTaskLogs(taskId: taskId)
            logs = response.logs
            isRunning = response.is_running
            if !isRunning { stopPolling() }
        } catch {
            logs += "\n[Error fetching logs: \(error.localizedDescription)]"
        }
        isLoading = false
    }

    private func startPolling() {
        pollTimer = Timer.scheduledTimer(withTimeInterval: 2.5, repeats: true) { _ in
            Task { @MainActor in await fetchLogs() }
        }
    }

    private func stopPolling() {
        pollTimer?.invalidate()
        pollTimer = nil
    }
}
