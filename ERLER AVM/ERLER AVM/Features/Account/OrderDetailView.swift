import Combine
import SwiftUI

struct OrderDetailView: View {
    let orderId: String

    @StateObject private var viewModel = OrderDetailViewModel()
    @Environment(\.openURL) private var openURL

    var body: some View {
        ZStack {
            Color(.systemGroupedBackground).edgesIgnoringSafeArea(.all)

            if viewModel.isLoading && viewModel.order == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage, viewModel.order == nil {
                VStack(spacing: 12) {
                    Text(error)
                        .foregroundColor(.red)
                        .multilineTextAlignment(.center)
                    Button("Tekrar Dene") {
                        Task { await viewModel.fetchOrder(orderId: orderId) }
                    }
                }
                .padding()
            } else if let order = viewModel.order {
                ScrollView {
                    VStack(spacing: 16) {
                        summaryCard(order)
                        paymentCard(order)
                        itemsCard(order)
                        totalsCard(order)
                    }
                    .padding()
                }
                .refreshable {
                    await viewModel.fetchOrder(orderId: orderId)
                }
            }
        }
        .navigationTitle("Sipariş Detayı")
        .navigationBarTitleDisplayMode(.inline)
        .task {
            await viewModel.fetchOrder(orderId: orderId)
        }
    }

    private func summaryCard(_ order: OrderDetail) -> some View {
        VStack(alignment: .leading, spacing: 12) {
            HStack {
                VStack(alignment: .leading, spacing: 4) {
                    Text(order.orderNumber)
                        .font(.title3.weight(.bold))
                    Text(order.createdAt)
                        .font(.footnote)
                        .foregroundColor(.secondary)
                }
                Spacer()
                statusPill(order.displayStatus, color: .blue)
            }

            HStack {
                Text("Genel toplam")
                    .foregroundColor(.secondary)
                Spacer()
                Text("\(order.grandTotal) ₺")
                    .font(.title3.weight(.bold))
                    .foregroundColor(ErlerTheme.red)
            }
        }
        .padding()
        .background(Color(.systemBackground))
        .cornerRadius(16)
    }

    private func paymentCard(_ order: OrderDetail) -> some View {
        VStack(alignment: .leading, spacing: 12) {
            HStack {
                Image(systemName: "creditcard")
                    .foregroundColor(ErlerTheme.red)
                Text("Ödeme")
                    .font(.headline)
                Spacer()
                statusPill(order.displayPaymentStatus, color: order.paymentStatus == "paid" ? .green : .orange)
            }

            if order.paymentRequired ?? false {
                Text("Ödeme tamamlanmadı. PayTR güvenli ödeme sayfasını açarak işlemi tamamlayabilirsiniz.")
                    .font(.footnote)
                    .foregroundColor(.secondary)

                Button {
                    Task {
                        if let url = await viewModel.preparePaymentURL(orderId: order.publicId) {
                            openURL(url)
                        }
                    }
                } label: {
                    HStack {
                        if viewModel.isPaymentLoading {
                            ProgressView()
                                .progressViewStyle(CircularProgressViewStyle(tint: .white))
                        } else {
                            Image(systemName: "lock.shield.fill")
                            Text("Güvenli Ödemeye Geç")
                        }
                    }
                    .fontWeight(.bold)
                    .frame(maxWidth: .infinity)
                    .padding()
                    .background(ErlerTheme.red)
                    .foregroundColor(.white)
                    .cornerRadius(12)
                }
                .disabled(viewModel.isPaymentLoading)
            }
        }
        .padding()
        .background(Color(.systemBackground))
        .cornerRadius(16)
    }

    private func itemsCard(_ order: OrderDetail) -> some View {
        VStack(alignment: .leading, spacing: 12) {
            Text("Ürünler")
                .font(.headline)

            ForEach(order.items) { item in
                HStack(alignment: .top, spacing: 12) {
                    Text("\(item.quantity)x")
                        .font(.subheadline.weight(.semibold))
                        .foregroundColor(ErlerTheme.red)
                        .frame(width: 36, alignment: .leading)

                    VStack(alignment: .leading, spacing: 4) {
                        Text(item.name)
                            .font(.subheadline.weight(.semibold))
                        if let sku = item.sku, !sku.isEmpty {
                            Text(sku)
                                .font(.caption)
                                .foregroundColor(.secondary)
                        }
                    }

                    Spacer()

                    Text("\(item.lineTotal) ₺")
                        .font(.subheadline.weight(.semibold))
                }
                .padding(.vertical, 6)

                if item.publicId != order.items.last?.publicId {
                    Divider()
                }
            }
        }
        .padding()
        .background(Color(.systemBackground))
        .cornerRadius(16)
    }

    private func totalsCard(_ order: OrderDetail) -> some View {
        VStack(spacing: 10) {
            totalRow("Ara toplam", "\(order.subtotal) ₺")
            totalRow("Kargo", "\(order.shippingTotal) ₺")
            if order.discountTotal != "0.00" {
                totalRow("İndirim", "-\(order.discountTotal) ₺")
            }
            Divider()
            totalRow("Toplam", "\(order.grandTotal) ₺", isBold: true)
        }
        .padding()
        .background(Color(.systemBackground))
        .cornerRadius(16)
    }

    private func totalRow(_ label: String, _ value: String, isBold: Bool = false) -> some View {
        HStack {
            Text(label)
                .foregroundColor(isBold ? .primary : .secondary)
            Spacer()
            Text(value)
                .fontWeight(isBold ? .bold : .regular)
        }
    }

    private func statusPill(_ text: String, color: Color) -> some View {
        Text(text)
            .font(.caption.weight(.semibold))
            .padding(.horizontal, 10)
            .padding(.vertical, 6)
            .background(color.opacity(0.13))
            .foregroundColor(color)
            .cornerRadius(999)
    }
}

@MainActor
final class OrderDetailViewModel: ObservableObject {
    @Published var order: OrderDetail?
    @Published var isLoading = false
    @Published var isPaymentLoading = false
    @Published var errorMessage: String?

    func fetchOrder(orderId: String) async {
        guard !isLoading else { return }

        isLoading = true
        errorMessage = nil

        do {
            order = try await APIClient.shared.request(.order(id: orderId), useCache: false)
            isLoading = false
        } catch let error as APIError {
            errorMessage = error.errorDescription
            isLoading = false
        } catch {
            errorMessage = "Sipariş detayı yüklenemedi."
            isLoading = false
        }
    }

    func preparePaymentURL(orderId: String) async -> URL? {
        isPaymentLoading = true
        errorMessage = nil

        do {
            let response: PaymentStartResponse = try await APIClient.shared.request(
                .startPayment(orderId: orderId),
                useCache: false
            )

            isPaymentLoading = false

            guard response.paymentRequired, let checkoutURL = response.checkoutURL else {
                errorMessage = "Bu sipariş için ödeme başlatılamıyor."
                return nil
            }

            return URL(string: checkoutURL)
        } catch let error as APIError {
            errorMessage = error.errorDescription
            isPaymentLoading = false
            return nil
        } catch {
            errorMessage = "Ödeme bağlantısı hazırlanamadı."
            isPaymentLoading = false
            return nil
        }
    }
}
