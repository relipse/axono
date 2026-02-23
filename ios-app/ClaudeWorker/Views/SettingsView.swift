import SwiftUI

struct SettingsView: View {
    @EnvironmentObject var apiClient: APIClient

    @State private var serverURL = ""
    @State private var apiKey = ""
    @State private var repoSource = "url"
    @State private var repoURL = ""
    @State private var localRepo = ""
    @State private var model = ""
    @State private var pushToRemote = false
    @State private var showSaved = false
    @State private var showLogoutAlert = false

    var body: some View {
        NavigationStack {
            Form {
                Section("Server") {
                    HStack {
                        Image(systemName: "globe")
                            .foregroundStyle(.secondary)
                        TextField("Server URL", text: $serverURL)
                            .keyboardType(.URL)
                            .autocapitalization(.none)
                            .autocorrectionDisabled()
                    }
                }

                Section("API Keys") {
                    HStack {
                        Image(systemName: "key.fill")
                            .foregroundStyle(.secondary)
                        SecureField("Anthropic API Key", text: $apiKey)
                            .autocapitalization(.none)
                    }
                }

                Section("Default Repository") {
                    Picker("Source", selection: $repoSource) {
                        Text("URL").tag("url")
                        Text("Local Path").tag("local")
                    }
                    .pickerStyle(.segmented)

                    if repoSource == "url" {
                        TextField("Repository URL", text: $repoURL)
                            .keyboardType(.URL)
                            .autocapitalization(.none)
                            .autocorrectionDisabled()
                    } else {
                        TextField("Local Path", text: $localRepo)
                            .autocapitalization(.none)
                            .autocorrectionDisabled()
                    }
                }

                Section("Defaults") {
                    TextField("Model (leave empty for default)", text: $model)
                        .autocapitalization(.none)
                        .autocorrectionDisabled()
                    Toggle("Push to Remote", isOn: $pushToRemote)
                }

                Section {
                    Button {
                        saveSettings()
                    } label: {
                        HStack {
                            Image(systemName: "checkmark.circle.fill")
                            Text("Save Settings")
                        }
                    }
                    .tint(.indigo)

                    if showSaved {
                        HStack {
                            Image(systemName: "checkmark")
                                .foregroundStyle(.green)
                            Text("Settings saved")
                                .foregroundStyle(.green)
                        }
                    }
                }

                Section {
                    Button(role: .destructive) {
                        showLogoutAlert = true
                    } label: {
                        HStack {
                            Image(systemName: "rectangle.portrait.and.arrow.right")
                            Text("Disconnect & Logout")
                        }
                    }
                }
            }
            .navigationTitle("Settings")
            .alert("Logout?", isPresented: $showLogoutAlert) {
                Button("Logout", role: .destructive) {
                    Task { try? await apiClient.logout() }
                }
                Button("Cancel", role: .cancel) { }
            } message: {
                Text("You'll need to reconnect and re-enter your password.")
            }
            .onAppear { loadSettings() }
        }
    }

    private func loadSettings() {
        let settings = ServerSettings.load()
        serverURL = settings.serverURL
        apiKey = settings.apiKey
        repoSource = settings.repoSource
        repoURL = settings.repoURL
        localRepo = settings.localRepo
        model = settings.model
        pushToRemote = settings.pushToRemote
    }

    private func saveSettings() {
        let settings = ServerSettings(
            serverURL: serverURL,
            apiKey: apiKey,
            repoSource: repoSource,
            repoURL: repoURL,
            localRepo: localRepo,
            model: model,
            pushToRemote: pushToRemote
        )
        settings.save()
        apiClient.serverURL = serverURL
        showSaved = true
        DispatchQueue.main.asyncAfter(deadline: .now() + 2) {
            showSaved = false
        }
    }
}
