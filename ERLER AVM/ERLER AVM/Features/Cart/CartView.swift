import SwiftUI

struct CartView: View {
    @StateObject private var viewModel = CartViewModel()
    
    var body: some View {
        NavigationStack {
            ZStack {
                ErlerTheme.softBackground.ignoresSafeArea()
                
                if viewModel.isLoading && viewModel.cart == nil {
                    ProgressView()
                        .tint(ErlerTheme.red)
                } else if let error = viewModel.errorMessage, viewModel.cart == nil {
                    VStack(spacing: 12) {
                        Image(systemName: "exclamationmark.triangle.fill")
                            .font(.title)
                            .foregroundColor(ErlerTheme.red)
                        Text(error)
                            .font(.subheadline)
                            .foregroundColor(ErlerTheme.ink)
                            .multilineTextAlignment(.center)
                        Button("Tekrar Dene") {
                            Task { await viewModel.fetchCart() }
                        }
                        .buttonStyle(.borderedProminent)
                        .tint(ErlerTheme.red)
                    }
                    .padding()
                } else if let cart = viewModel.cart, cart.items.isEmpty {
                    VStack(spacing: 16) {
                        Image(systemName: "cart")
                            .font(.system(size: 58, weight: .semibold))
                            .foregroundColor(ErlerTheme.red)
                            .frame(width: 92, height: 92)
                            .background(Color.white)
                            .clipShape(Circle())
                            .overlay(Circle().stroke(ErlerTheme.line, lineWidth: 1))

                        Text("Sepetiniz boş")
                            .font(.title2)
                            .fontWeight(.bold)
                            .foregroundColor(ErlerTheme.ink)

                        Text("Ürün eklediğinizde sepetiniz burada görünecek.")
                            .font(.subheadline)
                            .foregroundColor(.secondary)
                            .multilineTextAlignment(.center)
                    }
                    .padding(.horizontal, 34)
                } else if let cart = viewModel.cart {
                    VStack(spacing: 0) {
                        ScrollView {
                            LazyVStack(spacing: 12) {
                                if let error = viewModel.errorMessage {
                                    Text(error)
                                        .font(.footnote.weight(.semibold))
                                        .foregroundColor(ErlerTheme.red)
                                        .frame(maxWidth: .infinity, alignment: .leading)
                                        .padding(12)
                                        .background(Color.white)
                                        .cornerRadius(ErlerTheme.radius)
                                }

                                ForEach(cart.items) { item in
                                    CartItemView(item: item, onUpdate: { newQty in
                                        Task { await viewModel.updateQuantity(publicId: item.publicId, quantity: newQty) }
                                    }, onDelete: {
                                        Task { await viewModel.removeItem(publicId: item.publicId) }
                                    })
                                }
                            }
                            .padding()
                        }
                        
                        VStack(spacing: 14) {
                            Rectangle()
                                .fill(ErlerTheme.line)
                                .frame(height: 1)

                            HStack {
                                VStack(alignment: .leading, spacing: 3) {
                                    Text("Toplam")
                                        .font(.caption.weight(.semibold))
                                        .foregroundColor(.secondary)

                                    Text("\(cart.count) ürün")
                                        .font(.footnote)
                                        .foregroundColor(.secondary)
                                }

                                Spacer()

                                Text("\(cart.subtotal) ₺")
                                    .font(.title2.weight(.black))
                                    .foregroundColor(ErlerTheme.red)
                            }
                            .padding(.horizontal)
                            
                            NavigationLink(destination: CheckoutView()) {
                                HStack {
                                    Image(systemName: "lock.shield.fill")
                                    Text("Ödemeye Geç")
                                }
                                .fontWeight(.bold)
                                    .frame(maxWidth: .infinity)
                                    .padding()
                                    .background(ErlerTheme.red)
                                    .foregroundColor(.white)
                                    .cornerRadius(ErlerTheme.radius)
                            }
                            .padding(.horizontal)
                            .padding(.bottom, 10)
                        }
                        .background(Color(.systemBackground))
                    }
                }
            }
            .navigationTitle("Sepetim")
            .navigationBarTitleDisplayMode(.large)
        }
        .task {
            await viewModel.fetchCart()
        }
    }
}

struct CartItemView: View {
    let item: CartItem
    let onUpdate: (Int) -> Void
    let onDelete: () -> Void
    
    var body: some View {
        HStack(spacing: 14) {
            ZStack {
                if let url = APIEnvironment.assetURL(item.image) {
                    AsyncImage(url: url) { image in
                        image
                            .resizable()
                            .aspectRatio(contentMode: .fill)
                    } placeholder: {
                        ProgressView()
                    }
                } else {
                    Image(systemName: "photo")
                        .foregroundColor(.gray)
                }
            }
            .frame(width: 82, height: 82)
            .background(Color(.systemGray5))
            .clipped()
            .cornerRadius(ErlerTheme.radius)
            
            VStack(alignment: .leading, spacing: 9) {
                Text(item.name)
                    .font(.subheadline)
                    .fontWeight(.semibold)
                    .foregroundColor(ErlerTheme.ink)
                    .lineLimit(2)
                
                Text("\(item.price) ₺")
                    .font(.headline.weight(.black))
                    .foregroundColor(ErlerTheme.red)
                
                HStack(spacing: 10) {
                    Button(action: { if item.quantity > 1 { onUpdate(item.quantity - 1) } }) {
                        Image(systemName: "minus")
                            .font(.caption.weight(.black))
                            .frame(width: 28, height: 28)
                            .background(ErlerTheme.softGrey)
                            .foregroundColor(item.quantity > 1 ? ErlerTheme.ink : .secondary)
                            .clipShape(Circle())
                    }

                    Text("\(item.quantity)")
                        .fontWeight(.semibold)
                        .frame(minWidth: 20)

                    Button(action: { if item.quantity < item.stockQuantity { onUpdate(item.quantity + 1) } }) {
                        Image(systemName: "plus")
                            .font(.caption.weight(.black))
                            .frame(width: 28, height: 28)
                            .background(ErlerTheme.red)
                            .foregroundColor(.white)
                            .clipShape(Circle())
                    }
                    .disabled(item.quantity >= item.stockQuantity)
                    
                    Spacer()
                    
                    Button(action: onDelete) {
                        Image(systemName: "trash")
                            .font(.system(size: 15, weight: .semibold))
                            .foregroundColor(ErlerTheme.red)
                            .frame(width: 34, height: 34)
                            .background(Color.red.opacity(0.08))
                            .clipShape(Circle())
                    }
                }
            }
        }
        .padding(12)
        .background(Color(.systemBackground))
        .cornerRadius(ErlerTheme.radius)
        .shadow(color: ErlerTheme.softShadow, radius: 12, x: 0, y: 8)
    }
}
