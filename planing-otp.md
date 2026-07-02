# Change Verification Flow to 6-Digit OTP Code

We are changing the account registration/verification flow from sending a temporary signed verification URL via email to sending a 6-digit OTP code via email. The user will be redirected to the OTP verification screen immediately after successful registration, and upon trying to log in with an inactive account.

## User Review Required

> [!IMPORTANT]
> The backend relies on SMTP configurations currently present in `ubsi_store_api/.env`. OTP codes will be generated as 6-digit random numbers (`mt_rand(0, 999999)` padded to 6 digits) and valid for 10 minutes.

## Proposed Changes

---

### Backend Components

#### [MODIFY] [AuthController.php](file:///c:/Users/lutpi/bsi-store/ubsi_store_api/app/Http/Controllers/Api/AuthController.php)

1. Implement the private method `generateAndSendOtp(User $user)`:
   - Generate a 6-digit random code.
   - Update the user's `otp_code` and `otp_expires_at` fields.
   - Send the OTP code via email using `Mail::send`.
2. Update the `register` method:
   - Replace the call to `$this->sendVerificationEmail($user)` with `$this->generateAndSendOtp($user)`.
   - Update the success JSON message to direct the user to check their email for the OTP code.
3. Update the `login` method:
   - When an inactive user (`is_active` is false) attempts to log in, automatically generate a new OTP using `$this->generateAndSendOtp($user)` and return the `'requires_otp' => true` status along with their `'email'`.

---

### Flutter App Frontend

#### [MODIFY] [register_screen.dart](file:///c:/Users/lutpi/bsi-store/bsi_store/lib/services/register_screen.dart)

1. Import `otp_verification_screen.dart`.
2. Update the success callback in the registration flow:
   - Instead of displaying a dialog and sending the user back to the login screen, direct the user immediately to `OtpVerificationScreen(email: email)`.

#### [MODIFY] [login_screen.dart](file:///c:/Users/lutpi/bsi-store/bsi_store/lib/services/login_screen.dart)

1. Import `otp_verification_screen.dart`.
2. Update the login failure callback:
   - If `authProvider.requiresOtpEmail` is not null, display a descriptive snackbar and navigate the user to `OtpVerificationScreen(email: authProvider.requiresOtpEmail!)`.

---

## Verification Plan

### Manual Verification
1. Run registration in the Flutter application. Verify the application automatically navigates to the OTP Verification Screen.
2. Check backend logs (`storage/logs/laravel.log`) or the receiver inbox to find the generated 6-digit OTP.
3. Enter the OTP code in the Flutter application to verify successful activation and login redirection.
4. Try to log in with an inactive/unverified account and verify it triggers a new OTP and navigates to the OTP verification screen.
