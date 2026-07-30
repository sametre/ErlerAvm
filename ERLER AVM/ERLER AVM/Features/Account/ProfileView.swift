import SwiftUI

struct ProfileView: View {
    @EnvironmentObject var appState: AppState
    
    var body: some View {
        NavigationStack {
            ScrollView {
                VStack(spacing: 18) {
                    accountHeader

                    VStack(spacing: 10) {
                        ProfileActionRow(
                            title: "Siparişlerim",
                            subtitle: "Sipariş ve ödeme durumları",
                            icon: "shippingbox.fill",
                            destination: AnyView(OrdersView())
                        )

                        ProfileActionRow(
                            title: "Adreslerim",
                            subtitle: "Teslimat ve fatura adresleri",
                            icon: "map.fill",
                            destination: AnyView(AddressBookView())
                        )

                        ProfileActionRow(
                            title: "Bildirimler",
                            subtitle: "Sipariş ve hesap bildirimleri",
                            icon: "bell.fill",
                            destination: AnyView(NotificationsView())
                        )
                    }

                    Button {
                        appState.logout()
                    } label: {
                        HStack(spacing: 10) {
                            Image(systemName: "rectangle.portrait.and.arrow.right")
                            Text("Çıkış Yap")
                        }
                        .font(.headline)
                        .foregroundColor(ErlerTheme.red)
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.white)
                        .cornerRadius(ErlerTheme.radius)
                        .overlay(
                            RoundedRectangle(cornerRadius: ErlerTheme.radius)
                                .stroke(Color.red.opacity(0.18), lineWidth: 1)
                        )
                    }
                }
                .padding()
            }
            .background(ErlerTheme.softBackground.ignoresSafeArea())
            .navigationTitle("Hesabım")
            .navigationBarTitleDisplayMode(.large)
        }
    }

    private var accountHeader: some View {
        HStack(spacing: 14) {
            Image("Logo")
                .resizable()
                .scaledToFit()
                .frame(width: 64, height: 64)
                .clipShape(RoundedRectangle(cornerRadius: ErlerTheme.radius, style: .continuous))

            VStack(alignment: .leading, spacing: 5) {
                Text(appState.currentUser?.name ?? "Kullanıcı")
                    .font(.title3.weight(.bold))
                    .foregroundColor(ErlerTheme.ink)
                    .lineLimit(1)

                Text(appState.currentUser?.email ?? "")
                    .font(.subheadline)
                    .foregroundColor(.secondary)
                    .lineLimit(1)
            }

            Spacer()
        }
        .erlerCard(padding: 14)
    }
}

private struct ProfileActionRow: View {
    let title: String
    let subtitle: String
    let icon: String
    let destination: AnyView

    var body: some View {
        NavigationLink(destination: destination) {
            HStack(spacing: 14) {
                Image(systemName: icon)
                    .font(.system(size: 16, weight: .bold))
                    .foregroundColor(ErlerTheme.red)
                    .frame(width: 38, height: 38)
                    .background(Color.red.opacity(0.09))
                    .clipShape(Circle())

                VStack(alignment: .leading, spacing: 3) {
                    Text(title)
                        .font(.headline)
                        .foregroundColor(ErlerTheme.ink)

                    Text(subtitle)
                        .font(.caption)
                        .foregroundColor(.secondary)
                }

                Spacer()

                Image(systemName: "chevron.right")
                    .font(.caption.weight(.bold))
                    .foregroundColor(.secondary)
            }
            .padding(14)
            .background(Color.white)
            .cornerRadius(ErlerTheme.radius)
            .shadow(color: ErlerTheme.softShadow, radius: 10, x: 0, y: 6)
        }
        .buttonStyle(.plain)
    }
}
