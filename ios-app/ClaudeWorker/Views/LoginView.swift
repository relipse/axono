import SwiftUI

struct LoginView: View {
    @EnvironmentObject var apiClient: APIClient
    @State private var serverURL = ""
    @State private var password = ""
    @State private var isLoading = false
    @State private var errorMessage: String?
    @State private var showPassword = false

    var body: some View {
        NavigationStack {
            ScrollView {
                VStack(spacing: 32) {
                    // Header
                    VStack(spacing: 12) {
                        Image(systemName: "terminal.fill")
                            .font(.system(size: 56))
                            .foregroundStyle(.indigo)

                        Text("Claude Worker")
                            .font(.largeTitle.bold())

                        Text("Automated Code Tasks")
                            .font(.subheadline)
                            .foregroundStyle(.secondary)
                    }
                    .padding(.top, 40)

                    // Form
                    VStack(spacing: 20) {
                        VStack(alignment: .leading, spacing: 6) {
                            Label("Server URL", systemImage: "globe")
                                .font(.caption.weight(.medium))
                                .foregroundStyle(.secondary)

                            TextField("https://your-server.com", text: $serverURL)
                                .textFieldStyle(.roundedBorder)
                                .keyboardType(.URL)
                                .textContentType(.URL)
                                .autocapitalization(.none)
                                .autocorrectionDisabled()
                        }

                        VStack(alignment: .leading, spacing: 6) {
                            Label("Admin Password", systemImage: "lock")
                                .font(.caption.weight(.medium))
                                .foregroundStyle(.secondary)

                            HStack {
                                if showPassword {
                                    TextField("Password", text: $password)
                                        .textFieldStyle(.roundedBorder)
                                        .autocapitalization(.none)
                                        .autocorrectionDisabled()
                                } else {
                                    SecureField("Password", text: $password)
                                        .textFieldStyle(.roundedBorder)
                                }

                                Button {
                                    showPassword.toggle()
                                } label: {
                                    Image(systemName: showPassword ? "eye.slash" : "eye")
                                        .foregroundStyle(.secondary)
                                }
                            }
                        }

                        if let error = errorMessage {
                            HStack {
                                Image(systemName: "exclamationmark.triangle.fill")
                                    .foregroundStyle(.red)
                                Text(error)
                                    .font(.caption)
                                    .foregroundStyle(.red)
                            }
                            .padding(12)
                            .background(.red.opacity(0.1))
                            .cornerRadius(8)
                        }

                        Button {
                            Task { await login() }
                        } label: {
                            HStack {
                                if isLoading {
                                    ProgressView()
                                        .tint(.white)
                                } else {
                                    Image(systemName: "arrow.right.circle.fill")
                                    Text("Connect")
                                }
                            }
                            .frame(maxWidth: .infinity)
                            .padding(.vertical, 14)
                            .background(.indigo)
                            .foregroundStyle(.white)
                            .cornerRadius(12)
                            .font(.headline)
                        }
                        .disabled(isLoading || serverURL.isEmpty || password.isEmpty)
                        .opacity(serverURL.isEmpty || password.isEmpty ? 0.6 : 1)
                    }
                    .padding(.horizontal, 24)
                }
            }
            .navigationBarTitleDisplayMode(.inline)
            .onAppear {
                let settings = ServerSettings.load()
                serverURL = settings.serverURL
            }
        }
    }

    private func login() async {
        isLoading = true
        errorMessage = nil

        let url = serverURL.trimmingCharacters(in: .whitespacesAndNewlines)
            .trimmingCharacters(in: CharacterSet(charactersIn: "/"))

        apiClient.serverURL = url

        var settings = ServerSettings.load()
        settings.serverURL = url
        settings.save()

        do {
            try await apiClient.login(password: password)
        } catch {
            errorMessage = error.localizedDescription
        }

        isLoading = false
    }
}
