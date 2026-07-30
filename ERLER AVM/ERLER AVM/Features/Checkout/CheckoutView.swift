import SwiftUI

struct CheckoutView: View {
    @StateObject private var viewModel = CheckoutViewModel()
    @Environment(\.dismiss) var dismiss
    @State private var isShowingPayment = false
    
    var body: some View {
        ScrollView {
            if let order = viewModel.successOrder {
                // Success State
                VStack(spacing: 24) {
                    Image(systemName: "checkmark.circle.fill")
                        .font(.system(size: 80))
                        .foregroundColor(.green)
                    
                    Text("Sipariş Alındı")
                        .font(.largeTitle)
                        .fontWeight(.bold)

                    Text("Sipariş Numaranız: \(order.orderNumber)")
                        .font(.headline)
                        .foregroundColor(.secondary)

                    Text(order.displayPaymentStatus)
                        .font(.subheadline.weight(.semibold))
                        .padding(.horizontal, 12)
                        .padding(.vertical, 7)
                        .background(Color.orange.opacity(0.14))
                        .foregroundColor(.orange)
                        .cornerRadius(999)

                    if order.paymentRequired ?? true {
                        Text("Siparişiniz oluşturuldu. Kart bilgilerinizi güvenli ödeme ekranında girerek işlemi tamamlayın. Kart numarası ve CVV ERLER AVM sistemlerinde saklanmaz.")
                            .font(.callout)
                            .multilineTextAlignment(.center)
                            .foregroundColor(.secondary)
                            .padding(.horizontal)

                        Button {
                            Task {
                                if await viewModel.preparePaymentURL() != nil {
                                    isShowingPayment = true
                                }
                            }
                        } label: {
                            HStack {
                                if viewModel.isLoading {
                                    ProgressView()
                                        .progressViewStyle(CircularProgressViewStyle(tint: .white))
                                } else {
                                    Image(systemName: "creditcard.fill")
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
                        .disabled(viewModel.isLoading)
                        .padding(.horizontal)
                        .sheet(isPresented: $isShowingPayment) {
                            if let paymentURL = viewModel.paymentURL {
                                SecurePaymentView(url: paymentURL)
                                    .ignoresSafeArea()
                            }
                        }
                    }

                    if let error = viewModel.errorMessage {
                        Text(error)
                            .foregroundColor(.red)
                            .font(.callout)
                            .multilineTextAlignment(.center)
                            .padding()
                            .frame(maxWidth: .infinity)
                            .background(Color.red.opacity(0.1))
                            .cornerRadius(10)
                            .padding(.horizontal)
                    }

                    Button("Alışverişe Dön") {
                        dismiss()
                    }
                    .padding()
                    .background(Color(.systemGray6))
                    .foregroundColor(.primary)
                    .cornerRadius(10)
                }
                .padding(.top, 60)
            } else {
                // Form State
                VStack(spacing: 20) {
                    if let error = viewModel.errorMessage {
                        Text(error)
                            .foregroundColor(.red)
                            .padding()
                            .frame(maxWidth: .infinity)
                            .background(Color.red.opacity(0.1))
                            .cornerRadius(10)
                    }
                    
                    VStack(alignment: .leading, spacing: 16) {
                        Text("Teslimat Bilgileri")
                            .font(.headline)
                        
                        TextField("Ad Soyad", text: $viewModel.fullName)
                            .textFieldStyle(RoundedBorderTextFieldStyle())
                        
                        TextField("Telefon", text: $viewModel.phone)
                            .keyboardType(.phonePad)
                            .textFieldStyle(RoundedBorderTextFieldStyle())
                        
                        HStack {
                            TextField("İl", text: $viewModel.city)
                                .textFieldStyle(RoundedBorderTextFieldStyle())
                            TextField("İlçe", text: $viewModel.district)
                                .textFieldStyle(RoundedBorderTextFieldStyle())
                        }

                        TextField("Posta Kodu", text: $viewModel.postalCode)
                            .keyboardType(.numberPad)
                            .textFieldStyle(RoundedBorderTextFieldStyle())
                        
                        TextField("Açık Adres", text: $viewModel.addressLine)
                            .textFieldStyle(RoundedBorderTextFieldStyle())

                        TextField("T.C. Kimlik No", text: $viewModel.identityNumber)
                            .keyboardType(.numberPad)
                            .textFieldStyle(RoundedBorderTextFieldStyle())

                        TextField("Sipariş Notu (opsiyonel)", text: $viewModel.notes, axis: .vertical)
                            .lineLimit(2...4)
                            .textFieldStyle(RoundedBorderTextFieldStyle())
                    }
                    .padding()
                    .background(Color(.systemBackground))
                    .cornerRadius(12)
                    .shadow(color: Color.black.opacity(0.05), radius: 5)
                    
                    Button(action: { Task { await viewModel.placeOrder() } }) {
                        HStack {
                            if viewModel.isLoading {
                                ProgressView().progressViewStyle(CircularProgressViewStyle(tint: .white))
                            } else {
                                Text("Siparişi Oluştur")
                            }
                        }
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(ErlerTheme.red)
                        .foregroundColor(.white)
                        .cornerRadius(12)
                        .font(.headline)
                    }
                    .disabled(viewModel.isLoading || !viewModel.canPlaceOrder)
                }
                .padding()
            }
        }
        .background(Color(.systemGroupedBackground).edgesIgnoringSafeArea(.all))
        .navigationTitle("Teslimat ve Ödeme")
    }
}
