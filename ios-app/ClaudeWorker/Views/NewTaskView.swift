import SwiftUI

struct NewTaskView: View {
    @EnvironmentObject var apiClient: APIClient
    @StateObject private var voiceManager = VoiceManager()

    @State private var taskDescription = ""
    @State private var branch = ""
    @State private var repoBranch = ""
    @State private var maxTurns = ""
    @State private var rebuild = false
    @State private var verbose = false
    @State private var showAdvanced = false

    @State private var isLaunching = false
    @State private var successMessage: String?
    @State private var errorMessage: String?

    // Loaded from settings
    @State private var apiKey = ""
    @State private var repoSource = "url"
    @State private var repoURL = ""
    @State private var localRepo = ""
    @State private var model = ""
    @State private var pushToRemote = false

    var body: some View {
        NavigationStack {
            ScrollView {
                VStack(spacing: 20) {
                    // Task prompt with voice
                    taskSection
                    // Repository settings
                    repoSection
                    // Advanced options
                    advancedSection
                    // Actions
                    actionSection
                    // Feedback
                    feedbackSection
                }
                .padding()
            }
            .navigationTitle("New Task")
            .onAppear { loadSettings() }
        }
    }

    // MARK: - Task Section

    private var taskSection: some View {
        VStack(alignment: .leading, spacing: 8) {
            HStack {
                Label("Task Description", systemImage: "text.alignleft")
                    .font(.headline)
                Spacer()
                voiceButton
            }

            ZStack(alignment: .topLeading) {
                if taskDescription.isEmpty {
                    Text("Describe what you want Claude to do...")
                        .foregroundStyle(.tertiary)
                        .padding(.top, 8)
                        .padding(.leading, 5)
                }
                TextEditor(text: $taskDescription)
                    .frame(minHeight: 120)
                    .scrollContentBackground(.hidden)
            }
            .padding(8)
            .background(Color(.systemGray6))
            .cornerRadius(10)
        }
        .onChange(of: voiceManager.transcript) { _, newValue in
            if !newValue.isEmpty {
                taskDescription = newValue
            }
        }
    }

    private var voiceButton: some View {
        Button {
            if !voiceManager.isAuthorized {
                voiceManager.requestAuthorization()
            }
            voiceManager.toggleRecording()
        } label: {
            Image(systemName: voiceManager.isRecording ? "mic.fill" : "mic")
                .font(.title2)
                .foregroundStyle(voiceManager.isRecording ? .red : .indigo)
                .symbolEffect(.pulse, isActive: voiceManager.isRecording)
        }
    }

    // MARK: - Repo Section

    private var repoSection: some View {
        VStack(alignment: .leading, spacing: 12) {
            Label("Repository", systemImage: "folder.fill")
                .font(.headline)

            Picker("Source", selection: $repoSource) {
                Text("URL").tag("url")
                Text("Local Path").tag("local")
            }
            .pickerStyle(.segmented)

            if repoSource == "url" {
                TextField("https://github.com/user/repo.git", text: $repoURL)
                    .textFieldStyle(.roundedBorder)
                    .keyboardType(.URL)
                    .autocapitalization(.none)
                    .autocorrectionDisabled()
            } else {
                TextField("/path/to/local/repo", text: $localRepo)
                    .textFieldStyle(.roundedBorder)
                    .autocapitalization(.none)
                    .autocorrectionDisabled()
            }

            HStack {
                Image(systemName: "key.fill")
                    .foregroundStyle(.secondary)
                SecureField("Anthropic API Key", text: $apiKey)
                    .textFieldStyle(.roundedBorder)
                    .autocapitalization(.none)
            }
        }
    }

    // MARK: - Advanced Section

    private var advancedSection: some View {
        DisclosureGroup("Advanced Options", isExpanded: $showAdvanced) {
            VStack(spacing: 12) {
                HStack {
                    Text("Branch")
                    Spacer()
                    TextField("auto-generated", text: $branch)
                        .textFieldStyle(.roundedBorder)
                        .frame(maxWidth: 200)
                        .autocapitalization(.none)
                }

                HStack {
                    Text("Repo Branch")
                    Spacer()
                    TextField("main", text: $repoBranch)
                        .textFieldStyle(.roundedBorder)
                        .frame(maxWidth: 200)
                        .autocapitalization(.none)
                }

                HStack {
                    Text("Model")
                    Spacer()
                    TextField("default", text: $model)
                        .textFieldStyle(.roundedBorder)
                        .frame(maxWidth: 200)
                        .autocapitalization(.none)
                }

                HStack {
                    Text("Max Turns")
                    Spacer()
                    TextField("auto", text: $maxTurns)
                        .textFieldStyle(.roundedBorder)
                        .frame(maxWidth: 100)
                        .keyboardType(.numberPad)
                }

                Toggle("Push to Remote", isOn: $pushToRemote)
                Toggle("Rebuild Docker Image", isOn: $rebuild)
                Toggle("Verbose Output", isOn: $verbose)
            }
            .padding(.top, 8)
        }
        .tint(.indigo)
    }

    // MARK: - Action Section

    private var actionSection: some View {
        VStack(spacing: 12) {
            Button {
                Task { await launchTask() }
            } label: {
                HStack {
                    if isLaunching {
                        ProgressView().tint(.white)
                    } else {
                        Image(systemName: "play.fill")
                    }
                    Text("Run Worker")
                }
                .frame(maxWidth: .infinity)
                .padding(.vertical, 14)
                .background(.indigo)
                .foregroundStyle(.white)
                .cornerRadius(12)
                .font(.headline)
            }
            .disabled(isLaunching || taskDescription.isEmpty || apiKey.isEmpty)
            .opacity(taskDescription.isEmpty || apiKey.isEmpty ? 0.6 : 1)

            Button {
                taskDescription = ""
                voiceManager.clearTranscript()
                successMessage = nil
                errorMessage = nil
            } label: {
                HStack {
                    Image(systemName: "xmark.circle")
                    Text("Clear")
                }
                .foregroundStyle(.secondary)
            }
        }
    }

    // MARK: - Feedback Section

    @ViewBuilder
    private var feedbackSection: some View {
        if let success = successMessage {
            HStack {
                Image(systemName: "checkmark.circle.fill")
                    .foregroundStyle(.green)
                Text(success)
                    .font(.callout)
            }
            .padding(12)
            .background(.green.opacity(0.1))
            .cornerRadius(8)
        }

        if let error = errorMessage {
            HStack {
                Image(systemName: "exclamationmark.triangle.fill")
                    .foregroundStyle(.red)
                Text(error)
                    .font(.callout)
                    .foregroundStyle(.red)
            }
            .padding(12)
            .background(.red.opacity(0.1))
            .cornerRadius(8)
        }

        if let voiceError = voiceManager.errorMessage {
            HStack {
                Image(systemName: "mic.slash")
                    .foregroundStyle(.orange)
                Text(voiceError)
                    .font(.caption)
                    .foregroundStyle(.orange)
            }
            .padding(8)
            .background(.orange.opacity(0.1))
            .cornerRadius(8)
        }
    }

    // MARK: - Actions

    private func launchTask() async {
        isLaunching = true
        successMessage = nil
        errorMessage = nil
        saveSettings()

        do {
            let turns = Int(maxTurns)
            let response = try await apiClient.launchTask(
                apiKey: apiKey,
                task: taskDescription,
                repoSource: repoSource,
                repoURL: repoSource == "url" ? repoURL : nil,
                localRepo: repoSource == "local" ? localRepo : nil,
                branch: branch.isEmpty ? nil : branch,
                repoBranch: repoBranch.isEmpty ? nil : repoBranch,
                model: model.isEmpty ? nil : model,
                maxTurns: turns,
                push: pushToRemote,
                rebuild: rebuild,
                verbose: verbose
            )
            successMessage = "Task launched: \(response.task_id)"
            taskDescription = ""
            voiceManager.clearTranscript()
        } catch {
            errorMessage = error.localizedDescription
        }

        isLaunching = false
    }

    private func loadSettings() {
        let settings = ServerSettings.load()
        apiKey = settings.apiKey
        repoSource = settings.repoSource
        repoURL = settings.repoURL
        localRepo = settings.localRepo
        model = settings.model
        pushToRemote = settings.pushToRemote
        voiceManager.requestAuthorization()
    }

    private func saveSettings() {
        var settings = ServerSettings.load()
        settings.apiKey = apiKey
        settings.repoSource = repoSource
        settings.repoURL = repoURL
        settings.localRepo = localRepo
        settings.model = model
        settings.pushToRemote = pushToRemote
        settings.save()
    }
}
