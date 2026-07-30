//
//  ContentView.swift
//  ERLER AVM
//
//  Created by ttsy0 on 6/25/26.
//

import SwiftUI

struct ContentView: View {
    @EnvironmentObject var appState: AppState

    init() {
        let appearance = UITabBarAppearance()
        appearance.configureWithOpaqueBackground()
        appearance.backgroundColor = UIColor.systemBackground
        appearance.shadowColor = UIColor.separator

        UITabBar.appearance().standardAppearance = appearance
        UITabBar.appearance().scrollEdgeAppearance = appearance
        UITabBar.appearance().tintColor = UIColor(ErlerTheme.red)
    }
    
    var body: some View {
        TabView {
            HomeView()
                .tabItem {
                    Label("Ana Sayfa", systemImage: "house")
                }
                
            CategoryListView()
                .tabItem {
                    Label("Kategoriler", systemImage: "square.grid.2x2")
                }
                
            CartView()
                .tabItem {
                    Label("Sepet", systemImage: "cart")
                }
                
            ProfileView()
                .tabItem {
                    Label("Hesabım", systemImage: "person")
                }
        }
        .accentColor(ErlerTheme.red)
    }
}

// No preview needed
