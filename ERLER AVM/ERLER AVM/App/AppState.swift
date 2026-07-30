import Foundation
import SwiftUI
import Combine
import OSLog

private let appStateLogger = Logger(subsystem: "com.erleravm.mobile", category: "AppState")

@MainActor
class AppState: ObservableObject {
    @Published var isLoggedIn: Bool = false
    @Published var currentUser: User?
    
    init() {
        self.isLoggedIn = TokenManager.shared.isLoggedIn
        setupNotificationObservers()
        
        if isLoggedIn {
            Task {
                await fetchUser()
            }
        }
    }
    
    private func setupNotificationObservers() {
        NotificationCenter.default.addObserver(
            forName: Notification.Name("UserDidLogout"),
            object: nil,
            queue: .main
        ) { [weak self] _ in
            Task { @MainActor [weak self] in
                self?.clearLocalSession()
            }
        }
    }
    
    func login(user: User, token: String) {
        if TokenManager.shared.saveToken(token) {
            self.currentUser = user
            self.isLoggedIn = true

            Task {
                await PushNotificationManager.shared.requestAuthorizationIfNeeded()
            }
        }
    }
    
    func logout() {
        self.currentUser = nil
        self.isLoggedIn = false

        Task {
            do {
                let _: EmptyResponse = try await APIClient.shared.request(.logout, useCache: false)
            } catch {
                appStateLogger.warning("Logout API failed, but local session was cleared: \(String(describing: error), privacy: .public)")
            }

            await MainActor.run {
                self.clearLocalSession()
            }
        }
    }

    private func clearLocalSession() {
        _ = TokenManager.shared.deleteToken()
        CacheManager.shared.clearAll()
        self.currentUser = nil
        self.isLoggedIn = false
    }
    
    func fetchUser() async {
        do {
            struct UserResponse: Codable {
                let user: User
            }
            let response: UserResponse = try await APIClient.shared.request(.user)
            self.currentUser = response.user

            await PushNotificationManager.shared.requestAuthorizationIfNeeded()
        } catch {
            appStateLogger.warning("Failed to fetch authenticated user: \(String(describing: error), privacy: .public)")
            // If unauthenticated, APIClient already posts UserDidLogout
        }
    }
}
