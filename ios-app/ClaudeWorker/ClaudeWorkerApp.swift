import SwiftUI

@main
struct ClaudeWorkerApp: App {
    @StateObject private var apiClient = APIClient.shared

    var body: some Scene {
        WindowGroup {
            ContentView()
                .environmentObject(apiClient)
        }
    }
}
