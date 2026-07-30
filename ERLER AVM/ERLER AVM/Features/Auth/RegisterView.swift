import SwiftUI

struct RegisterView: View {
    @EnvironmentObject var appState: AppState
    @Environment(\.presentationMode) var presentationMode
    
    @State private var name = ""
    @State private var email = ""
    @State private var password = ""
    @State private var passwordConfirmation = ""
    @State private var isLoading = false
    @State private var errorMessage: String?
    
    var body: some View {
        ScrollView {
            VStack(spacing: 24) {
                // Header
                VStack(spacing: 8) {
                    Image("Logo")
                        .resizable()
                        .scaledToFit()
                        .frame(width: 74, height: 74)

                    Text("Kayıt Ol")
                        .font(.largeTitle)
                        .fontWeight(.bold)
                        
                    Text("Yeni bir hesap oluşturun")
                        .foregroundColor(.secondary)
                }
                .padding(.top, 20)
                .padding(.bottom, 20)
                
                if let errorMessage = errorMessage {
                    Text(errorMessage)
                        .foregroundColor(.red)
                        .font(.callout)
                        .padding()
                        .frame(maxWidth: .infinity)
                        .background(Color.red.opacity(0.1))
                        .cornerRadius(10)
                }
                
                // Form
                VStack(spacing: 16) {
                    TextField("Ad Soyad", text: $name)
                        .padding()
                        .background(Color(.systemGray6))
                        .cornerRadius(10)
                        
                    TextField("E-posta Adresi", text: $email)
                        .keyboardType(.emailAddress)
                        .autocapitalization(.none)
                        .padding()
                        .background(Color(.systemGray6))
                        .cornerRadius(10)
                    
                    SecureField("Şifre", text: $password)
                        .padding()
                        .background(Color(.systemGray6))
                        .cornerRadius(10)
                        
                    SecureField("Şifre Tekrarı", text: $passwordConfirmation)
                        .padding()
                        .background(Color(.systemGray6))
                        .cornerRadius(10)
                }
                
                // Register Button
                Button(action: register) {
                    HStack {
                        if isLoading {
                            ProgressView()
                                .progressViewStyle(CircularProgressViewStyle(tint: .white))
                        } else {
                            Text("Kayıt Ol")
                        }
                    }
                    .frame(maxWidth: .infinity)
                    .padding()
                    .background(ErlerTheme.red)
                    .foregroundColor(.white)
                    .cornerRadius(10)
                    .font(.headline)
                }
                .disabled(isLoading || name.isEmpty || email.isEmpty || password.isEmpty || password != passwordConfirmation)
                .padding(.top, 8)
                
                Button(action: {
                    presentationMode.wrappedValue.dismiss()
                }) {
                    Text("Zaten hesabınız var mı? Giriş Yapın")
                        .foregroundColor(ErlerTheme.red)
                        .font(.subheadline)
                }
                .padding(.top)
            }
            .padding(.horizontal, 24)
        }
        .navigationBarTitleDisplayMode(.inline)
    }
    
    private func register() {
        let request = RegisterRequest(
            name: name.trimmingCharacters(in: .whitespacesAndNewlines),
            email: email.trimmingCharacters(in: .whitespacesAndNewlines).lowercased(),
            password: password,
            passwordConfirmation: passwordConfirmation
        )

        guard request.password == request.passwordConfirmation else {
            errorMessage = "Şifreler uyuşmuyor."
            return
        }

        guard !request.name.isEmpty, !request.email.isEmpty, !request.password.isEmpty else {
            return
        }
        
        isLoading = true
        errorMessage = nil
        
        Task {
            do {
                let response: AuthResponse = try await APIClient.shared.request(.register(data: request), useCache: false)
                
                await MainActor.run {
                    self.isLoading = false
                    appState.login(user: response.user, token: response.token)
                    presentationMode.wrappedValue.dismiss()
                }
            } catch let error as APIError {
                await MainActor.run {
                    self.errorMessage = error.errorDescription
                    self.isLoading = false
                }
            } catch {
                await MainActor.run {
                    self.errorMessage = "Bir hata oluştu."
                    self.isLoading = false
                }
            }
        }
    }
}

private struct RegisterRequest: Encodable {
    let name: String
    let email: String
    let password: String
    let passwordConfirmation: String

    enum CodingKeys: String, CodingKey {
        case name
        case email
        case password
        case passwordConfirmation = "password_confirmation"
    }
}
