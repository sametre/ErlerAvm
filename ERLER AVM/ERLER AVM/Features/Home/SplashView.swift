import SwiftUI

struct SplashView: View {
    @EnvironmentObject var appState: AppState
    @State private var isActive = false
    @State private var logoOpacity = 0.0
    @State private var logoScale: CGFloat = 0.86
    @State private var contentOffset: CGFloat = 20
    @State private var contentOpacity = 0.0
    
    var body: some View {
        if isActive {
            if appState.isLoggedIn {
                ContentView()
            } else {
                LoginView()
            }
        } else {
            ZStack {
                ErlerTheme.red
                    .ignoresSafeArea()
                
                VStack(spacing: 24) {
                    Spacer()

                    Image("Logo")
                        .resizable()
                        .scaledToFit()
                        .frame(width: 156, height: 156)
                        .clipShape(RoundedRectangle(cornerRadius: 34, style: .continuous))
                        .overlay(
                            RoundedRectangle(cornerRadius: 34, style: .continuous)
                                .stroke(Color.white, lineWidth: 8)
                        )
                        .shadow(color: Color.black.opacity(0.24), radius: 28, x: 0, y: 18)
                        .scaleEffect(logoScale)
                        .opacity(logoOpacity)

                    Text("ERLER AVM")
                        .font(.system(size: 38, weight: .black, design: .rounded))
                        .foregroundColor(.white)
                    
                    VStack(spacing: 14) {
                        Text("Alışverişe hazırlanıyor")
                            .font(.title3.weight(.bold))
                            .foregroundColor(.white)
                        
                        Text("Karacabey'in kırmızı beyaz alışveriş deneyimi")
                            .font(.subheadline)
                            .foregroundColor(.white.opacity(0.84))
                            .multilineTextAlignment(.center)
                        
                        ProgressView()
                            .progressViewStyle(CircularProgressViewStyle(tint: .white))
                            .scaleEffect(1.18)
                            .padding(.top, 12)
                    }
                    .frame(maxWidth: .infinity)
                    .padding(.horizontal, 32)
                    .offset(y: contentOffset)
                    .opacity(contentOpacity)

                    Spacer()
                    Spacer().frame(height: 32)
                }
            }
            .onAppear {
                withAnimation(.spring(response: 0.72, dampingFraction: 0.78)) {
                    logoOpacity = 1.0
                    logoScale = 1.0
                }

                withAnimation(.easeOut(duration: 0.62).delay(0.42)) {
                    contentOpacity = 1.0
                    contentOffset = 0
                }
                
                DispatchQueue.main.asyncAfter(deadline: .now() + 2.5) {
                    withAnimation(.easeInOut(duration: 0.35)) {
                        isActive = true
                    }
                }
            }
        }
    }
}
