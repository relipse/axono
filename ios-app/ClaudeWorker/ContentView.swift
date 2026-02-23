import SwiftUI

struct ContentView: View {
    @EnvironmentObject var apiClient: APIClient

    var body: some View {
        Group {
            if apiClient.isAuthenticated {
                DashboardView()
            } else {
                LoginView()
            }
        }
        .animation(.easeInOut, value: apiClient.isAuthenticated)
    }
}
