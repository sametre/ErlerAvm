import SwiftUI

enum ErlerTheme {
    static let red = Color(red: 0.93, green: 0.02, blue: 0.08)
    static let deepRed = Color(red: 0.68, green: 0.00, blue: 0.04)
    static let mutedRed = Color(red: 0.78, green: 0.03, blue: 0.07)
    static let ink = Color(red: 0.10, green: 0.10, blue: 0.12)
    static let graphite = Color(red: 0.28, green: 0.28, blue: 0.31)
    static let line = Color(red: 0.88, green: 0.88, blue: 0.90)
    static let card = Color(.systemBackground)
    static let softGrey = Color(red: 0.96, green: 0.96, blue: 0.97)
    static let softBackground = Color(.systemGroupedBackground)
    static let radius: CGFloat = 8
    static let softShadow = Color.black.opacity(0.07)
}

extension View {
    func erlerPrimaryButton(disabled: Bool = false) -> some View {
        self
            .fontWeight(.semibold)
            .frame(maxWidth: .infinity)
            .padding(.vertical, 14)
            .background(disabled ? Color.gray.opacity(0.45) : ErlerTheme.red)
            .foregroundColor(.white)
            .cornerRadius(ErlerTheme.radius)
    }

    func erlerCard(padding: CGFloat = 14) -> some View {
        self
            .padding(padding)
            .background(ErlerTheme.card)
            .cornerRadius(ErlerTheme.radius)
            .shadow(color: ErlerTheme.softShadow, radius: 14, x: 0, y: 8)
    }

    func erlerToolbarIcon() -> some View {
        self
            .font(.system(size: 16, weight: .semibold))
            .frame(width: 36, height: 36)
            .background(Color.white.opacity(0.16))
            .foregroundColor(.white)
            .clipShape(Circle())
    }
}
