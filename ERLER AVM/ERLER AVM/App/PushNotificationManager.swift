import Foundation
import Combine
import OSLog
import UIKit
import UserNotifications

private let pushLogger = Logger(subsystem: "com.erleravm.mobile", category: "Push")

final class AppDelegate: NSObject, UIApplicationDelegate {
    func application(
        _ application: UIApplication,
        didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data
    ) {
        PushNotificationManager.shared.registerDeviceToken(deviceToken)
    }

    func application(
        _ application: UIApplication,
        didFailToRegisterForRemoteNotificationsWithError error: Error
    ) {
        pushLogger.error("Remote notification registration failed: \(error.localizedDescription, privacy: .public)")
    }
}

@MainActor
final class PushNotificationManager: ObservableObject {
    static let shared = PushNotificationManager()

    @Published private(set) var authorizationStatus: UNAuthorizationStatus = .notDetermined

    private init() {}

    func refreshAuthorizationStatus() async {
        let settings = await UNUserNotificationCenter.current().notificationSettings()
        authorizationStatus = settings.authorizationStatus
    }

    func requestAuthorizationIfNeeded() async {
        await refreshAuthorizationStatus()

        guard authorizationStatus == .notDetermined else {
            if authorizationStatus == .authorized || authorizationStatus == .provisional {
                UIApplication.shared.registerForRemoteNotifications()
            }
            return
        }

        do {
            let granted = try await UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .badge, .sound])
            await refreshAuthorizationStatus()

            if granted {
                UIApplication.shared.registerForRemoteNotifications()
            }
        } catch {
            pushLogger.error("Notification authorization failed: \(error.localizedDescription, privacy: .public)")
        }
    }

    nonisolated func registerDeviceToken(_ deviceToken: Data) {
        let token = deviceToken.map { String(format: "%02.2hhx", $0) }.joined()

        Task {
            await submitDeviceToken(token)
        }
    }

    private func submitDeviceToken(_ token: String) async {
        guard TokenManager.shared.getToken() != nil else { return }

        let request = PushDeviceTokenRequest(
            platform: "ios",
            token: token,
            deviceId: UIDevice.current.identifierForVendor?.uuidString,
            appVersion: Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String,
            locale: Locale.current.identifier,
            timezone: TimeZone.current.identifier
        )

        do {
            let _: PushDeviceRegistrationResponse = try await APIClient.shared.request(
                .registerPushDevice(data: request),
                useCache: false
            )
        } catch {
            pushLogger.error("Push device token sync failed: \(error.localizedDescription, privacy: .public)")
        }
    }
}
