<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Edit Customer Profile
 * Modern Form UI Matching Desktop (LG) & Mobile (SM) System
 */
?>

<div class="modern-form-wrapper">

    <!-- Top Header Bar -->
    <div class="modern-form-header-bar">
        <div class="modern-header-left">
            <a href="<?= url('/office/customers/' . $customer['id']) ?>" class="modern-back-btn" title="Back to Customer Profile" aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="modern-header-meta">
                <h1 class="modern-form-title"><?= e(__('customer.edit_title', ['name' => $customer['name']], 'Edit Customer Profile')) ?></h1>
                <p class="modern-form-subtitle"><?= e(__('customer.edit_subtitle', [], 'Update verified client details, contact, and address.')) ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 50px; background: #E8F4F0; color: #176B5B; font-size: 0.88rem; font-weight: 700;">
                <i class="fa-solid fa-id-badge"></i> <?= e($customer['customer_code']) ?>
            </span>
        </div>
    </div>

    <form action="<?= url('/office/customers/' . $customer['id']) ?>" method="POST" id="editCustomerForm">
        <?= csrf_field() ?>

        <div class="modern-form-card" style="margin-bottom: 2rem;">
            <!-- Section 1: Personal Details -->
            <div class="modern-section-header">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Personal Details</h2>
                    <p class="modern-section-sub">Customer full legal name, gender, salutation, and date of birth.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Title / Salutation -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="title">Salutation</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-signature"></i></span>
                        <select id="title" name="title" class="modern-select-pill">
                            <option value="">Select Salutation</option>
                            <?php 
                            $titles = ['Mr' => 'Mr.', 'Mrs' => 'Mrs.', 'Ms' => 'Ms.', 'Dr' => 'Dr.', 'Prof' => 'Prof.'];
                            foreach ($titles as $t => $lbl): ?>
                                <option value="<?= $t ?>" <?= ($old['title'] ?? $customer['title']) === $t ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Full Name -->
                <div class="modern-form-group modern-col-span-2">
                    <label class="modern-form-label" for="name">Full Name <span class="req">*</span></label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="name" name="name" class="modern-input-pill" required value="<?= e($old['name'] ?? $customer['name']) ?>" placeholder="e.g. John Doe">
                    </div>
                    <?php if (!empty($errors['name'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['name'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Date of Birth -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="date_of_birth">Date of Birth</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-calendar"></i></span>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="modern-input-pill" value="<?= e($old['date_of_birth'] ?? $customer['date_of_birth'] ?? '') ?>">
                    </div>
                </div>

                <!-- Gender -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="gender">Gender</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-venus-mars"></i></span>
                        <select id="gender" name="gender" class="modern-select-pill">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($old['gender'] ?? $customer['gender']) === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($old['gender'] ?? $customer['gender']) === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($old['gender'] ?? $customer['gender']) === 'other' ? 'selected' : '' ?>>Other</option>
                            <option value="non_binary" <?= ($old['gender'] ?? $customer['gender']) === 'non_binary' ? 'selected' : '' ?>>Non-Binary</option>
                            <option value="prefer_not_to_say" <?= ($old['gender'] ?? $customer['gender']) === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer Not to Say</option>
                        </select>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="status">Account Status <span class="req">*</span></label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <select id="status" name="status" class="modern-select-pill" required>
                            <option value="active" <?= ($old['status'] ?? $customer['status']) === 'active' ? 'selected' : '' ?>>Active Profile</option>
                            <option value="inactive" <?= ($old['status'] ?? $customer['status']) === 'inactive' ? 'selected' : '' ?>>Inactive (Archived)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Contact Information -->
            <div class="modern-section-header" style="margin-top: 1.5rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Contact Information</h2>
                    <p class="modern-section-sub">Primary phone, alternate numbers, and billing email address.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Primary Mobile -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="phone">Primary Mobile Number <span class="req">*</span></label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" id="phone" name="phone" class="modern-input-pill" required value="<?= e($old['phone'] ?? $customer['phone']) ?>" placeholder="e.g. +91 9876543210">
                    </div>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['phone'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Alternate Phone -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="alternate_phone">Alternate Phone</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-phone-volume"></i></span>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="modern-input-pill" value="<?= e($old['alternate_phone'] ?? $customer['alternate_phone'] ?? '') ?>" placeholder="e.g. +91 9123456789">
                    </div>
                </div>

                <!-- Email -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="email">Email Address</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="modern-input-pill" value="<?= e($old['email'] ?? $customer['email'] ?? '') ?>" placeholder="e.g. john@example.com">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 3: Residential & Billing Address -->
            <div class="modern-section-header" style="margin-top: 1.5rem;">
                <div class="modern-section-badge">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="modern-section-meta">
                    <h2 class="modern-section-title">Residential &amp; Billing Address</h2>
                    <p class="modern-section-sub">Full postal address used for bookings, tours, and automated GST billing.</p>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- Address -->
                <div class="modern-form-group modern-col-span-2">
                    <label class="modern-form-label" for="address_line">Street Address / Landmark</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <input type="text" id="address_line" name="address_line" class="modern-input-pill" value="<?= e($old['address_line'] ?? $customer['address_line'] ?? '') ?>" placeholder="Street address, apartment, suite...">
                    </div>
                </div>

                <!-- City -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="city">City</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-city"></i></span>
                        <input type="text" id="city" name="city" class="modern-input-pill" value="<?= e($old['city'] ?? $customer['city'] ?? '') ?>" placeholder="e.g. Mumbai">
                    </div>
                </div>
            </div>

            <div class="modern-form-grid-3">
                <!-- State -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="state">State</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                        <input type="text" id="state" name="state" class="modern-input-pill" value="<?= e($old['state'] ?? $customer['state'] ?? '') ?>" placeholder="e.g. Maharashtra">
                    </div>
                </div>

                <!-- Country -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="country">Country</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-earth-americas"></i></span>
                        <input type="text" id="country" name="country" class="modern-input-pill" value="<?= e($old['country'] ?? $customer['country'] ?? 'India') ?>" placeholder="e.g. India">
                    </div>
                </div>

                <!-- Postal Code -->
                <div class="modern-form-group">
                    <label class="modern-form-label" for="postal_code">Postal Code</label>
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                        <input type="text" id="postal_code" name="postal_code" class="modern-input-pill" value="<?= e($old['postal_code'] ?? $customer['postal_code'] ?? '') ?>" placeholder="e.g. 400001">
                    </div>
                </div>
            </div>

            <!-- Marketing Preferences -->
            <div style="background: #F8FAFD; border: 1.5px solid #DCE6F2; border-radius: 14px; padding: 14px 18px; margin-top: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                    <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1" <?= (!empty($old['marketing_consent']) || !empty($customer['marketing_consent'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer; accent-color: #176B5B;">
                    <div>
                        <span style="font-size: 0.9rem; font-weight: 700; color: #0C2B52; display: block;">Send Tour Updates &amp; Digital Receipts</span>
                        <span style="font-size: 0.78rem; color: #64748B; display: block;">Client has consented to receive automated booking confirmations via SMS and WhatsApp</span>
                    </div>
                </label>
            </div>

            <!-- Form Actions -->
            <div class="modern-form-actions">
                <a href="<?= url('/office/customers/' . $customer['id']) ?>" class="modern-btn-cancel">
                    <i class="fa-solid fa-xmark"></i> Cancel &amp; Discard
                </a>
                <button type="submit" class="modern-btn-submit">
                    <i class="fa-solid fa-check"></i> <?= __('form.save_changes', [], 'Save Changes') ?>
                </button>
            </div>
        </div>
    </form>
</div>
