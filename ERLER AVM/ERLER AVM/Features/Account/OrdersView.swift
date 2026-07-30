import SwiftUI

struct OrdersView: View {
    @StateObject private var viewModel = OrdersViewModel()
    
    var body: some View {
        ZStack {
            Color(.systemGroupedBackground).edgesIgnoringSafeArea(.all)
            
            if viewModel.isLoading && viewModel.orders.isEmpty {
                ProgressView()
            } else if let error = viewModel.errorMessage, viewModel.orders.isEmpty {
                VStack {
                    Text(error).foregroundColor(.red)
                    Button("Tekrar Dene") { Task { await viewModel.fetchOrders() } }
                }
            } else if viewModel.orders.isEmpty {
                VStack {
                    Image(systemName: "shippingbox")
                        .font(.system(size: 60))
                        .foregroundColor(.gray)
                    Text("Henüz siparişiniz bulunmuyor.")
                        .foregroundColor(.secondary)
                }
            } else {
                List(viewModel.orders, id: \.publicId) { order in
                    NavigationLink(destination: OrderDetailView(orderId: order.publicId)) {
                        VStack(alignment: .leading, spacing: 10) {
                            HStack(alignment: .top) {
                                VStack(alignment: .leading, spacing: 4) {
                                    Text(order.orderNumber)
                                        .font(.headline)
                                    if let createdAt = order.createdAt {
                                        Text(createdAt)
                                            .font(.caption)
                                            .foregroundColor(.secondary)
                                    }
                                }
                                Spacer()
                                Text("\(order.grandTotal) ₺")
                                    .fontWeight(.bold)
                                    .foregroundColor(ErlerTheme.red)
                            }

                            HStack(spacing: 8) {
                                statusPill(order.displayStatus, color: .blue)
                                statusPill(order.displayPaymentStatus, color: order.paymentStatus == "paid" ? .green : .orange)
                            }
                        }
                        .padding(.vertical, 6)
                    }
                }
                .listStyle(InsetGroupedListStyle())
            }
        }
        .navigationTitle("Siparişlerim")
        .task {
            await viewModel.fetchOrders()
        }
    }

    private func statusPill(_ text: String, color: Color) -> some View {
        Text(text)
            .font(.caption2.weight(.semibold))
            .padding(.horizontal, 8)
            .padding(.vertical, 5)
            .background(color.opacity(0.13))
            .foregroundColor(color)
            .cornerRadius(999)
    }
}
