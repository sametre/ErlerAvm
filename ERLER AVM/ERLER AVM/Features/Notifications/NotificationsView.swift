import SwiftUI
import Combine

struct NotificationsView: View {
    @StateObject private var viewModel = NotificationsViewModel()

    var body: some View {
        ZStack {
            ErlerTheme.softBackground.edgesIgnoringSafeArea(.all)

            if viewModel.isLoading && viewModel.notifications.isEmpty {
                ProgressView()
            } else if let error = viewModel.errorMessage, viewModel.notifications.isEmpty {
                VStack(spacing: 12) {
                    Text(error)
                        .foregroundColor(.red)
                    Button("Tekrar Dene") {
                        Task { await viewModel.fetchNotifications() }
                    }
                }
            } else if viewModel.notifications.isEmpty {
                VStack(spacing: 14) {
                    Image(systemName: "bell")
                        .font(.system(size: 58))
                        .foregroundColor(.secondary)
                    Text("Henüz bildiriminiz yok.")
                        .foregroundColor(.secondary)
                }
            } else {
                List {
                    if viewModel.unreadCount > 0 {
                        Section {
                            Button {
                                Task { await viewModel.markAllRead() }
                            } label: {
                                Label("Tümünü okundu işaretle", systemImage: "checkmark.circle")
                                    .foregroundColor(ErlerTheme.red)
                            }
                        }
                    }

                    Section {
                        ForEach(viewModel.notifications) { notification in
                            NotificationRow(notification: notification)
                                .contentShape(Rectangle())
                                .onTapGesture {
                                    if notification.isUnread {
                                        Task { await viewModel.markRead(notification) }
                                    }
                                }
                        }
                    }
                }
                .listStyle(.insetGrouped)
                .refreshable {
                    await viewModel.fetchNotifications()
                }
            }
        }
        .navigationTitle("Bildirimler")
        .task {
            await viewModel.fetchNotifications()
        }
    }
}

private struct NotificationRow: View {
    let notification: AppNotification

    var body: some View {
        HStack(alignment: .top, spacing: 12) {
            Image(systemName: notification.isUnread ? "bell.badge.fill" : "bell")
                .foregroundColor(notification.isUnread ? ErlerTheme.red : .secondary)
                .frame(width: 28)

            VStack(alignment: .leading, spacing: 5) {
                HStack {
                    Text(notification.title)
                        .font(.subheadline.weight(.semibold))
                    Spacer()
                    if notification.isUnread {
                        Circle()
                            .fill(ErlerTheme.red)
                            .frame(width: 8, height: 8)
                    }
                }

                if let body = notification.body {
                    Text(body)
                        .font(.footnote)
                        .foregroundColor(.secondary)
                        .lineLimit(3)
                }

                if let createdAt = notification.createdAt {
                    Text(createdAt)
                        .font(.caption2)
                        .foregroundColor(.secondary)
                }
            }
        }
        .padding(.vertical, 6)
    }
}

@MainActor
final class NotificationsViewModel: ObservableObject {
    @Published var notifications: [AppNotification] = []
    @Published var unreadCount = 0
    @Published var isLoading = false
    @Published var errorMessage: String?

    func fetchNotifications() async {
        guard !isLoading else { return }

        isLoading = true
        errorMessage = nil

        do {
            let response: NotificationsResponse = try await APIClient.shared.request(.notifications)
            notifications = response.notifications
            unreadCount = response.unreadCount
            isLoading = false
        } catch let error as APIError {
            errorMessage = error.errorDescription
            isLoading = false
        } catch {
            errorMessage = "Bildirimler yüklenemedi."
            isLoading = false
        }
    }

    func markRead(_ notification: AppNotification) async {
        do {
            let updated: AppNotification = try await APIClient.shared.request(.markNotificationRead(id: notification.publicId), useCache: false)
            if let index = notifications.firstIndex(where: { $0.publicId == updated.publicId }) {
                notifications[index] = updated
            }
            unreadCount = max(0, unreadCount - 1)
        } catch let error as APIError {
            errorMessage = error.errorDescription
        } catch {
            errorMessage = "Bildirim okundu işaretlenemedi."
        }
    }

    func markAllRead() async {
        do {
            let response: NotificationUnreadCountResponse = try await APIClient.shared.request(.markAllNotificationsRead, useCache: false)
            unreadCount = response.unreadCount
            await fetchNotifications()
        } catch let error as APIError {
            errorMessage = error.errorDescription
        } catch {
            errorMessage = "Bildirimler güncellenemedi."
        }
    }
}
