import SwiftUI

enum DashboardTab: String, CaseIterable {
    case newTask = "New Task"
    case liveTasks = "Live Tasks"
    case runs = "Runs"
    case docker = "Docker"
    case settings = "Settings"

    var icon: String {
        switch self {
        case .newTask: return "plus.circle.fill"
        case .liveTasks: return "bolt.circle.fill"
        case .runs: return "checkmark.circle.fill"
        case .docker: return "shippingbox.fill"
        case .settings: return "gearshape.fill"
        }
    }
}

struct DashboardView: View {
    @EnvironmentObject var apiClient: APIClient
    @State private var selectedTab: DashboardTab = .newTask

    var body: some View {
        TabView(selection: $selectedTab) {
            NewTaskView()
                .tabItem {
                    Label(DashboardTab.newTask.rawValue, systemImage: DashboardTab.newTask.icon)
                }
                .tag(DashboardTab.newTask)

            LiveTasksView()
                .tabItem {
                    Label(DashboardTab.liveTasks.rawValue, systemImage: DashboardTab.liveTasks.icon)
                }
                .tag(DashboardTab.liveTasks)

            RunsView()
                .tabItem {
                    Label(DashboardTab.runs.rawValue, systemImage: DashboardTab.runs.icon)
                }
                .tag(DashboardTab.runs)

            DockerView()
                .tabItem {
                    Label(DashboardTab.docker.rawValue, systemImage: DashboardTab.docker.icon)
                }
                .tag(DashboardTab.docker)

            SettingsView()
                .tabItem {
                    Label(DashboardTab.settings.rawValue, systemImage: DashboardTab.settings.icon)
                }
                .tag(DashboardTab.settings)
        }
        .tint(.indigo)
    }
}
