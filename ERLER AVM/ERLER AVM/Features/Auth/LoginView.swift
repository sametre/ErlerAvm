import SwiftUI

struct LoginView: View {
    @EnvironmentObject var appState: AppState
    @Environment(\.dismiss) var dismiss
    
    @State private var email = ""
    @State private var password = ""
    @State private var isLoading = false
    @State private var errorMessage: String?
    
    var body: some View {
        NavigationView {
            ScrollView {
                VStack(spacing: 24) {
                    // Logo Header
                    VStack(spacing: 8) {
                        Image("Logo")
                            .resizable()
                            .scaledToFit()
                            .frame(width: 82, height: 82)
                        
                        Text("ERLER AVM")
                            .font(.title)
                            .fontWeight(.bold)
                            
                        Text("Hesabınıza giriş yapın")
                            .foregroundColor(.secondary)
                    }
                    .padding(.top, 40)
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
                        
                        HStack {
                            Spacer()
                            Link("Şifremi Unuttum", destination: URL(string: "\(APIEnvironment.webBaseURL)/forgot-password")!)
                            .font(.footnote)
                            .foregroundColor(ErlerTheme.red)
                        }
                    }
                    
                    // Login Button
                    Button(action: login) {
                        HStack {
                            if isLoading {
                                ProgressView()
                                    .progressViewStyle(CircularProgressViewStyle(tint: .white))
                            } else {
                                Text("Giriş Yap")
                            }
                        }
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(ErlerTheme.red)
                        .foregroundColor(.white)
                        .cornerRadius(10)
                        .font(.headline)
                    }
                    .disabled(isLoading || email.isEmpty || password.isEmpty)
                    
                    // Register Link
                    NavigationLink(destination: RegisterView()) {
                        HStack {
                            Text("Hesabınız yok mu?")
                                .foregroundColor(.secondary)
                            Text("Kayıt Ol")
                                .fontWeight(.semibold)
                                .foregroundColor(ErlerTheme.red)
                        }
                        .padding(.top)
                    }
                }
                .padding(.horizontal, 24)
            }
            .navigationBarHidden(true)
        }
    }
    
    private func login() {
        let request = LoginRequest(
            email: email.trimmingCharacters(in: .whitespacesAndNewlines).lowercased(),
            password: password
        )

        guard !request.email.isEmpty, !request.password.isEmpty else { return }
        
        isLoading = true
        errorMessage = nil
        
        Task {
            do {
                let response: AuthResponse = try await APIClient.shared.request(.login(data: request), useCache: false)
                
                await MainActor.run {
                    self.isLoading = false
                    appState.login(user: response.user, token: response.token)
                    dismiss()
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

private struct LoginRequest: Encodable {
    let email: String
    let password: String
}
