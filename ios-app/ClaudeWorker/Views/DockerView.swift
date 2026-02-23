import SwiftUI

struct DockerView: View {
    @EnvironmentObject var apiClient: APIClient
    @State private var containers: [DockerContainer] = []
    @State private var isLoading = false
    @State private var errorMessage: String?
    @State private var selectedLogs: (name: String, content: String)?
    @State private var showStopAll = false

    var body: some View {
        NavigationStack {
            Group {
                if isLoading && containers.isEmpty {
                    ProgressView("Loading containers...")
                } else if containers.isEmpty {
                    ContentUnavailableView(
                        "No Docker Containers",
                        systemImage: "shippingbox",
                        description: Text("Running Claude Worker containers will appear here")
                    )
                } else {
                    containerList
                }
            }
            .navigationTitle("Docker")
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    if !containers.isEmpty {
                        Button(role: .destructive) {
                            showStopAll = true
                        } label: {
                            Label("Stop All", systemImage: "stop.circle.fill")
                        }
                        .tint(.red)
                    }
                }
                ToolbarItem(placement: .topBarTrailing) {
                    Button {
                        Task { await loadContainers() }
                    } label: {
                        Image(systemName: "arrow.clockwise")
                    }
                }
            }
            .sheet(item: Binding(
                get: { selectedLogs.map { IdentifiableText(id: $0.name, content: $0.content, title: "Logs") } },
                set: { _ in selectedLogs = nil }
            )) { item in
                LogView(title: "Docker: \(item.id)", content: item.content)
            }
            .alert("Stop All Containers?", isPresented: $showStopAll) {
                Button("Stop All", role: .destructive) {
                    Task { await stopAll() }
                }
                Button("Cancel", role: .cancel) { }
            } message: {
                Text("This will stop all running Claude Worker Docker containers.")
            }
            .onAppear { Task { await loadContainers() } }
        }
    }

    private var containerList: some View {
        List(containers) { container in
            VStack(alignment: .leading, spacing: 8) {
                HStack {
                    Image(systemName: "shippingbox.fill")
                        .foregroundStyle(.indigo)
                    Text(container.name)
                        .font(.body.bold())
                    Spacer()
                }

                if let containerId = container.container_id {
                    Text(String(containerId.prefix(12)))
                        .font(.caption.monospaced())
                        .foregroundStyle(.secondary)
                }

                HStack {
                    if let status = container.status {
                        Label(status, systemImage: "circle.fill")
                            .font(.caption)
                            .foregroundStyle(status.lowercased().contains("up") ? .green : .orange)
                    }
                    if let created = container.created {
                        Spacer()
                        Text(created)
                            .font(.caption2)
                            .foregroundStyle(.secondary)
                    }
                }

                HStack(spacing: 12) {
                    Button {
                        Task { await loadLogs(container.name) }
                    } label: {
                        Label("Logs", systemImage: "doc.text")
                            .font(.caption.bold())
                    }
                    .buttonStyle(.bordered)
                    .tint(.indigo)

                    Button(role: .destructive) {
                        Task { await stopContainer(container.name) }
                    } label: {
                        Label("Stop", systemImage: "stop.fill")
                            .font(.caption.bold())
                    }
                    .buttonStyle(.bordered)
                    .tint(.red)
                }
            }
            .padding(.vertical, 4)
        }
    }

    private func loadContainers() async {
        if containers.isEmpty { isLoading = true }
        do {
            containers = try await apiClient.fetchDockerWorkers()
            errorMessage = nil
        } catch {
            errorMessage = error.localizedDescription
        }
        isLoading = false
    }

    private func loadLogs(_ name: String) async {
        do {
            let logs = try await apiClient.fetchDockerLogs(name: name)
            selectedLogs = (name: name, content: logs)
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    private func stopContainer(_ name: String) async {
        do {
            try await apiClient.stopDockerContainer(name: name)
            await loadContainers()
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    private func stopAll() async {
        do {
            try await apiClient.stopAllDockerContainers()
            await loadContainers()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}
