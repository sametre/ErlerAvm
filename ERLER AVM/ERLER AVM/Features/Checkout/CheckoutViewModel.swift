import Foundation
import Combine

@MainActor
class CheckoutViewModel: ObservableObject {
    @Published var fullName = ""
    @Published var phone = ""
    @Published var city = ""
    @Published var district = ""
    @Published var postalCode = ""
    @Published var addressLine = ""
    @Published var identityNumber = ""
    @Published var notes = ""
    @Published var isLoading = false
    @Published var errorMessage: String?
    @Published var successOrder: OrderShort?
    @Published var paymentURL: URL?

    var canPlaceOrder: Bool {
        trimmed(fullName).isEmpty == false
            && trimmed(phone).count >= 10
            && trimmed(city).isEmpty == false
            && trimmed(district).isEmpty == false
            && trimmed(addressLine).count >= 10
            && identityDigits.count == 11
    }
    
    func placeOrder() async {
        guard canPlaceOrder else {
            errorMessage = "Teslimat ve fatura bilgilerini eksiksiz girin."
            return
        }

        isLoading = true
        errorMessage = nil
        
        do {
            let requestData = CheckoutRequest(
                shippingFullName: trimmed(fullName),
                shippingPhone: trimmed(phone),
                shippingCity: trimmed(city),
                shippingDistrict: trimmed(district),
                shippingPostalCode: optionalTrimmed(postalCode),
                shippingAddressLine: trimmed(addressLine),
                billingSameAsShipping: true,
                billingType: "individual",
                billingFullName: trimmed(fullName),
                billingIdentityNumber: identityDigits,
                billingCompanyName: nil,
                billingTaxNumber: nil,
                billingTaxOffice: nil,
                billingCity: nil,
                billingDistrict: nil,
                billingPostalCode: nil,
                billingAddressLine: nil,
                notes: optionalTrimmed(notes)
            )
            
            let response: OrderResponse = try await APIClient.shared.request(.checkout(data: requestData))
            self.successOrder = response.order
            self.isLoading = false
        } catch let error as APIError {
            self.errorMessage = error.errorDescription
            self.isLoading = false
        } catch {
            self.errorMessage = "Sipariş oluşturulamadı."
            self.isLoading = false
        }
    }

    func preparePaymentURL() async -> URL? {
        guard let order = successOrder else { return nil }

        isLoading = true
        errorMessage = nil

        do {
            let response: PaymentStartResponse = try await APIClient.shared.request(
                .startPayment(orderId: order.publicId),
                useCache: false
            )

            guard response.paymentRequired, let checkoutURL = response.checkoutURL, let url = URL(string: checkoutURL) else {
                self.errorMessage = "Bu sipariş için ödeme başlatılamıyor."
                self.isLoading = false
                return nil
            }

            self.paymentURL = url
            self.isLoading = false
            return url
        } catch let error as APIError {
            self.errorMessage = error.errorDescription
            self.isLoading = false
            return nil
        } catch {
            self.errorMessage = "Ödeme bağlantısı hazırlanamadı."
            self.isLoading = false
            return nil
        }
    }

    private var identityDigits: String {
        identityNumber.filter(\.isNumber)
    }

    private func trimmed(_ value: String) -> String {
        value.trimmingCharacters(in: .whitespacesAndNewlines)
    }

    private func optionalTrimmed(_ value: String) -> String? {
        let trimmedValue = trimmed(value)

        return trimmedValue.isEmpty ? nil : trimmedValue
    }
}

private struct CheckoutRequest: Encodable {
    let shippingFullName: String
    let shippingPhone: String
    let shippingCity: String
    let shippingDistrict: String
    let shippingPostalCode: String?
    let shippingAddressLine: String
    let billingSameAsShipping: Bool
    let billingType: String
    let billingFullName: String
    let billingIdentityNumber: String?
    let billingCompanyName: String?
    let billingTaxNumber: String?
    let billingTaxOffice: String?
    let billingCity: String?
    let billingDistrict: String?
    let billingPostalCode: String?
    let billingAddressLine: String?
    let notes: String?

    enum CodingKeys: String, CodingKey {
        case shippingFullName = "shipping_full_name"
        case shippingPhone = "shipping_phone"
        case shippingCity = "shipping_city"
        case shippingDistrict = "shipping_district"
        case shippingPostalCode = "shipping_postal_code"
        case shippingAddressLine = "shipping_address_line"
        case billingSameAsShipping = "billing_same_as_shipping"
        case billingType = "billing_type"
        case billingFullName = "billing_full_name"
        case billingIdentityNumber = "billing_identity_number"
        case billingCompanyName = "billing_company_name"
        case billingTaxNumber = "billing_tax_number"
        case billingTaxOffice = "billing_tax_office"
        case billingCity = "billing_city"
        case billingDistrict = "billing_district"
        case billingPostalCode = "billing_postal_code"
        case billingAddressLine = "billing_address_line"
        case notes
    }
}
