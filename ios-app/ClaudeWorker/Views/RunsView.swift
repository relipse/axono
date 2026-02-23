import SwiftUI

struct RunsView: View {
    @EnvironmentObject var apiClient: APIClient
    @State private var runs: [CompletedRun] = []
    @State private var isLoading = false
    @State private var errorMessage: String?

    @State private var selectedDiff: (id: String, content: String)?
    @State private var selectedSummary: (id: String, content: String)?
    @State private var selectedLog: (id: String, content: String)?
    @State private var showingDelete: String?

    var body: some View {
        NavigationStack {
            Group {
                if isLoading && runs.isEmpty {
                    ProgressView("Loading runs...")
                } else if runs.isEmpty {
                    ContentUnavailableView(
                        "No Completed Runs",
                        systemImage: "tray",
                        description: Text("Completed worker runs will appear here")
                    )
                } else {
                    runsList
                }
            }
            .navigationTitle("Completed Runs")
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    Button {
                        Task { await loadRuns() }
                    } label: {
                        Image(systemName: "arrow.clockwise")
                    }
                }
            }
            .sheet(item: Binding(
                get: { selectedDiff.map { IdentifiableText(id: $0.id, content: $0.content, title: "Diff") } },
                set: { _ in selectedDiff = nil }
            )) { item in
                DiffView(title: "Diff: \(item.id)", content: item.content)
            }
            .sheet(item: Binding(
                get: { selectedSummary.map { IdentifiableText(id: $0.id, content: $0.content, title: "Summary") } },
                set: { _ in selectedSummary = nil }
            )) { item in
                LogView(title: "Summary: \(item.id)", content: item.content)
            }
            .sheet(item: Binding(
                get: { selectedLog.map { IdentifiableText(id: $0.id, content: $0.content, title: "Log") } },
                set: { _ in selectedLog = nil }
            )) { item in
                LogView(title: "Log: \(item.id)", content: item.content)
            }
            .alert("Delete Run?", isPresented: Binding(
                get: { showingDelete != nil },
                set: { if !$0 { showingDelete = nil } }
            )) {
                Button("Delete", role: .destructive) {
                    if let id = showingDelete {
                        Task { await deleteRun(id) }
                    }
                }
                Button("Cancel", role: .cancel) { }
            } message: {
                Text("This will permanently delete all files for this run.")
            }
            .onAppear { Task { await loadRuns() } }
        }
    }

    private var runsList: some View {
        List(runs) { run in
            VStack(alignment: .leading, spacing: 8) {
                HStack {
                    statusBadge(run.statusLabel)
                    Spacer()
                    if run.filesChanged > 0 {
                        Label("\(run.filesChanged) files", systemImage: "doc")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                    }
                }

                Text(run.run_id)
                    .font(.caption.monospaced())
                    .foregroundStyle(.secondary)

                if let task = run.info?.task {
                    Text(task)
                        .font(.callout)
                        .lineLimit(2)
                }

                if let branch = run.info?.branch_name, !branch.isEmpty {
                    Label(branch, systemImage: "arrow.triangle.branch")
                        .font(.caption)
                        .foregroundStyle(.indigo)
                }

                // Action buttons
                ScrollView(.horizontal, showsIndicators: false) {
                    HStack(spacing: 8) {
                        if run.has_diff == true {
                            Button {
                                Task { await loadDiff(run.run_id) }
                            } label: {
                                Label("Diff", systemImage: "chevron.left.forwardslash.chevron.right")
                                    .font(.caption.bold())
                            }
                            .buttonStyle(.bordered)
                            .tint(.indigo)
                        }

                        Button {
                            Task { await loadSummary(run.run_id) }
                        } label: {
                            Label("Summary", systemImage: "doc.text")
                                .font(.caption.bold())
                        }
                        .buttonStyle(.bordered)
                        .tint(.blue)

                        Button {
                            Task { await loadLog(run.run_id) }
                        } label: {
                            Label("Log", systemImage: "text.alignleft")
                                .font(.caption.bold())
                        }
                        .buttonStyle(.bordered)
                        .tint(.gray)

                        Button(role: .destructive) {
                            showingDelete = run.run_id
                        } label: {
                            Label("Delete", systemImage: "trash")
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

    private func statusBadge(_ status: String) -> some View {
        HStack(spacing: 4) {
            Circle()
                .fill(statusColor(status))
                .frame(width: 8, height: 8)
            Text(status.capitalized)
                .font(.caption.bold())
                .foregroundStyle(statusColor(status))
        }
        .padding(.horizontal, 8)
        .padding(.vertical, 4)
        .background(statusColor(status).opacity(0.15))
        .cornerRadius(6)
    }

    private func statusColor(_ status: String) -> Color {
        switch status {
        case "success": return .green
        case "error": return .red
        default: return .gray
        }
    }

    private func loadRuns() async {
        if runs.isEmpty { isLoading = true }
        do {
            runs = try await apiClient.fetchRuns()
        } catch {
            errorMessage = error.localizedDescription
        }
        isLoading = false
    }

    private func loadDiff(_ runId: String) async {
        do {
            let diff = try await apiClient.fetchRunDiff(runId: runId)
            selectedDiff = (id: runId, content: diff)
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    private func loadSummary(_ runId: String) async {
        do {
            let summary = try await apiClient.fetchRunSummary(runId: runId)
            selectedSummary = (id: runId, content: summary)
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    private func loadLog(_ runId: String) async {
        do {
            let log = try await apiClient.fetchRunLogs(runId: runId)
            selectedLog = (id: runId, content: log)
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    private func deleteRun(_ runId: String) async {
        do {
            try await apiClient.deleteRun(runId: runId)
            runs.removeAll { $0.run_id == runId }
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}

struct IdentifiableText: Identifiable {
    let id: String
    let content: String
    let title: String
}
