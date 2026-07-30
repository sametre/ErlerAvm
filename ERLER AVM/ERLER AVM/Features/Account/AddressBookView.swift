import SwiftUI
import Combine

struct AddressBookView: View {
    @StateObject private var viewModel = AddressBookViewModel()
    @State private var isShowingForm = false
    @State private var editingAddress: CustomerAddress?

    var body: some View {
        ZStack {
            ErlerTheme.softBackground.edgesIgnoringSafeArea(.all)

            if viewModel.isLoading && viewModel.addresses.isEmpty {
                ProgressView()
            } else if let error = viewModel.errorMessage, viewModel.addresses.isEmpty {
                VStack(spacing: 12) {
                    Text(error)
                        .foregroundColor(.red)
                    Button("Tekrar Dene") {
                        Task { await viewModel.fetchAddresses() }
                    }
                }
            } else if viewModel.addresses.isEmpty {
                VStack(spacing: 16) {
                    Image(systemName: "map")
                        .font(.system(size: 60))
                        .foregroundColor(.secondary)
                    Text("Kayıtlı adresiniz yok.")
                        .font(.headline)
                    Button("Adres Ekle") {
                        editingAddress = nil
                        isShowingForm = true
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(ErlerTheme.red)
                }
            } else {
                List {
                    ForEach(viewModel.addresses) { address in
                        AddressRow(address: address)
                            .contentShape(Rectangle())
                            .onTapGesture {
                                editingAddress = address
                                isShowingForm = true
                            }
                            .swipeActions(edge: .trailing, allowsFullSwipe: false) {
                                Button(role: .destructive) {
                                    Task { await viewModel.deleteAddress(address) }
                                } label: {
                                    Label("Sil", systemImage: "trash")
                                }

                                Button {
                                    editingAddress = address
                                    isShowingForm = true
                                } label: {
                                    Label("Düzenle", systemImage: "pencil")
                                }
                                .tint(.orange)
                            }
                    }
                }
                .listStyle(.insetGrouped)
                .refreshable {
                    await viewModel.fetchAddresses()
                }
            }
        }
        .navigationTitle("Adreslerim")
        .toolbar {
            ToolbarItem(placement: .navigationBarTrailing) {
                Button {
                    editingAddress = nil
                    isShowingForm = true
                } label: {
                    Image(systemName: "plus")
                }
                .foregroundColor(ErlerTheme.red)
            }
        }
        .sheet(isPresented: $isShowingForm) {
            NavigationView {
                AddressFormView(address: editingAddress) { request in
                    await viewModel.saveAddress(request, existing: editingAddress)
                }
            }
        }
        .task {
            await viewModel.fetchAddresses()
        }
    }
}

private struct AddressRow: View {
    let address: CustomerAddress

    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            HStack {
                Text(address.title)
                    .font(.headline)
                if address.isDefault {
                    Text("Varsayılan")
                        .font(.caption2.weight(.bold))
                        .padding(.horizontal, 8)
                        .padding(.vertical, 4)
                        .background(ErlerTheme.red.opacity(0.12))
                        .foregroundColor(ErlerTheme.red)
                        .clipShape(Capsule())
                }
                Spacer()
                Image(systemName: "chevron.right")
                    .font(.caption)
                    .foregroundColor(.secondary)
            }

            Text("\(address.fullName) - \(address.phone)")
                .font(.subheadline)
                .foregroundColor(.secondary)

            Text(address.addressLine)
                .font(.subheadline)
                .lineLimit(2)

            Text("\(address.district) / \(address.city)")
                .font(.caption)
                .foregroundColor(.secondary)
        }
        .padding(.vertical, 6)
    }
}

private struct AddressFormView: View {
    @Environment(\.dismiss) private var dismiss

    @State private var type: String
    @State private var title: String
    @State private var fullName: String
    @State private var phone: String
    @State private var city: String
    @State private var district: String
    @State private var postalCode: String
    @State private var addressLine: String
    @State private var isDefault: Bool
    @State private var validationMessage: String?
    @State private var isSaving = false

    private let address: CustomerAddress?
    private let onSave: (CustomerAddressRequest) async -> Bool

    init(address: CustomerAddress?, onSave: @escaping (CustomerAddressRequest) async -> Bool) {
        self.address = address
        self.onSave = onSave
        _type = State(initialValue: address?.type ?? "individual")
        _title = State(initialValue: address?.title ?? "")
        _fullName = State(initialValue: address?.fullName ?? "")
        _phone = State(initialValue: address?.phone ?? "")
        _city = State(initialValue: address?.city ?? "")
        _district = State(initialValue: address?.district ?? "")
        _postalCode = State(initialValue: address?.postalCode ?? "")
        _addressLine = State(initialValue: address?.addressLine ?? "")
        _isDefault = State(initialValue: address?.isDefault ?? false)
    }

    var body: some View {
        Form {
            if let validationMessage {
                Section {
                    Text(validationMessage)
                        .foregroundColor(.red)
                }
            }

            Section("Adres tipi") {
                Picker("Tip", selection: $type) {
                    Text("Bireysel").tag("individual")
                    Text("Kurumsal").tag("corporate")
                }
                .pickerStyle(.segmented)
            }

            Section("İletişim") {
                TextField("Başlık", text: $title)
                TextField("Ad soyad / yetkili", text: $fullName)
                TextField("Telefon", text: $phone)
                    .keyboardType(.phonePad)
            }

            Section("Adres") {
                TextField("İl", text: $city)
                TextField("İlçe", text: $district)
                TextField("Posta kodu", text: $postalCode)
                    .keyboardType(.numberPad)
                TextField("Açık adres", text: $addressLine, axis: .vertical)
                    .lineLimit(3...5)
                Toggle("Varsayılan adres", isOn: $isDefault)
            }
        }
        .navigationTitle(address == nil ? "Adres Ekle" : "Adresi Düzenle")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .cancellationAction) {
                Button("Vazgeç") { dismiss() }
            }
            ToolbarItem(placement: .confirmationAction) {
                Button(isSaving ? "Kaydediliyor" : "Kaydet") {
                    Task { await save() }
                }
                .disabled(isSaving)
                .foregroundColor(ErlerTheme.red)
            }
        }
    }

    private func save() async {
        validationMessage = nil

        guard title.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty == false,
              fullName.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty == false,
              phone.trimmingCharacters(in: .whitespacesAndNewlines).count >= 10,
              city.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty == false,
              district.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty == false,
              addressLine.trimmingCharacters(in: .whitespacesAndNewlines).count >= 10 else {
            validationMessage = "Adres, telefon, il/ilçe ve açık adres bilgilerini eksiksiz girin."
            return
        }

        isSaving = true

        let request = CustomerAddressRequest(
            type: type,
            title: title,
            fullName: fullName,
            phone: phone,
            city: city,
            district: district,
            postalCode: postalCode.isEmpty ? nil : postalCode,
            addressLine: addressLine,
            isDefault: isDefault
        )

        let saved = await onSave(request)
        isSaving = false

        if saved {
            dismiss()
        } else {
            validationMessage = "Adres kaydedilemedi. Bilgileri kontrol edip tekrar deneyin."
        }
    }
}

@MainActor
final class AddressBookViewModel: ObservableObject {
    @Published var addresses: [CustomerAddress] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    func fetchAddresses() async {
        guard !isLoading else { return }

        isLoading = true
        errorMessage = nil

        do {
            addresses = try await APIClient.shared.request(.addresses)
            isLoading = false
        } catch let error as APIError {
            errorMessage = error.errorDescription
            isLoading = false
        } catch {
            errorMessage = "Adresler yüklenemedi."
            isLoading = false
        }
    }

    func saveAddress(_ request: CustomerAddressRequest, existing address: CustomerAddress?) async -> Bool {
        errorMessage = nil

        do {
            if let address {
                let updated: CustomerAddress = try await APIClient.shared.request(.updateAddress(id: address.publicId, data: request), useCache: false)
                if let index = addresses.firstIndex(where: { $0.publicId == updated.publicId }) {
                    addresses[index] = updated
                }
            } else {
                let created: CustomerAddress = try await APIClient.shared.request(.createAddress(data: request), useCache: false)
                addresses.insert(created, at: 0)
            }

            await fetchAddresses()
            return true
        } catch let error as APIError {
            errorMessage = error.errorDescription
            return false
        } catch {
            errorMessage = "Adres kaydedilemedi."
            return false
        }
    }

    func deleteAddress(_ address: CustomerAddress) async {
        errorMessage = nil

        do {
            let _: EmptyResponse = try await APIClient.shared.request(.deleteAddress(id: address.publicId), useCache: false)
            addresses.removeAll { $0.publicId == address.publicId }
            await fetchAddresses()
        } catch let error as APIError {
            errorMessage = error.errorDescription
        } catch {
            errorMessage = "Adres silinemedi."
        }
    }
}
