//
//  ERLER_AVMApp.swift
//  ERLER AVM
//
//  Created by ttsy0 on 6/25/26.
//

import SwiftUI

@main
struct ERLER_AVMApp: App {
    @UIApplicationDelegateAdaptor(AppDelegate.self) private var appDelegate
    @StateObject private var appState = AppState()
    @StateObject private var pushNotifications = PushNotificationManager.shared
    
    var body: some Scene {
        WindowGroup {
            SplashView()
                .environmentObject(appState)
                .environmentObject(pushNotifications)
        }
    }
}
