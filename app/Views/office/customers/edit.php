<?php
/**
 * VoyageGo — Edit Customer Profile
 */
?>

<!-- Breadcrumb -->
<nav class="breadcrumb-container" aria-label="Breadcrumb" style="margin-bottom: 1rem;">
    <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: var(--vg-text-muted);">
        <li>
            <a href="<?= url('/office/dashboard') ?>" style="color: var(--vg-blue-500); text-decoration: none; font-weight: 500;">Dashboard</a>
        </li>
        <li style="color: var(--vg-text-muted);">&rsaquo;</li>
        <li>
            <a href="<?= url('/office/customers') ?>" style="color: var(--vg-blue-500); text-decoration: none; font-weight: 500;">Customers</a>
        </li>
        <li style="color: var(--vg-text-muted);">&rsaquo;</li>
        <li>
            <a href="<?= url('/office/customers/' . $customer['id']) ?>" style="color: var(--vg-blue-500); text-decoration: none; font-weight: 500;"><?= e($customer['customer_code']) ?></a>
        </li>
        <li style="color: var(--vg-text-muted);">&rsaquo;</li>
        <li style="color: var(--vg-navy-900); font-weight: 600;" aria-current="page">Edit Profile</li>
    </ol>
</nav>

<!-- Page Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900); margin: 0 0 0.25rem 0;">
            Edit Customer Profile — <?= e($customer['customer_code']) ?>
        </h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem; margin: 0;">Update contact information, billing address, or profile status.</p>
    </div>
    <div>
        <a href="<?= url('/office/customers/' . $customer['id']) ?>" class="btn btn-outline">
            &larr; Cancel &amp; Return
        </a>
    </div>
</div>

<form action="<?= url('/office/customers/' . $customer['id']) ?>" method="POST" id="editCustomerForm">
    <?= csrf_field() ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;" class="customer-form-grid">
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            <!-- Basic Information -->
            <div class="vg-card">
                <div class="card-header">
                    <span class="card-title">01. Basic Information</span>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1rem; margin-bottom: 1.25rem;" class="vg-split-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="title">Title</label>
                            <select id="title" name="title" class="form-select">
                                <option value="">—</option>
                                <?php foreach (['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Lord', 'Lady'] as $t): ?>
                                    <option value="<?= $t ?>" <?= ($old['title'] ?? $customer['title']) === $t ? 'selected' : '' ?>><?= $t ?>.</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="name">Full Legal Name <span style="color: var(--vg-danger);">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required value="<?= e($old['name'] ?? $customer['name']) ?>">
                            <?php if (!empty($errors['name'])): ?>
                                <div style="color: var(--vg-danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['name'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="vg-split-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?= e($old['date_of_birth'] ?? $customer['date_of_birth'] ?? '') ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-select">
                                <option value="">Prefer not to specify</option>
                                <?php foreach (['male' => 'Male', 'female' => 'Female', 'non_binary' => 'Non-binary', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $k => $label): ?>
                                    <option value="<?= $k ?>" <?= ($old['gender'] ?? $customer['gender']) === $k ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="vg-card">
                <div class="card-header">
                    <span class="card-title">02. Contact Information</span>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <div class="vg-split-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="phone">Primary Mobile Number <span style="color: var(--vg-danger);">*</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control" required value="<?= e($old['phone'] ?? $customer['phone']) ?>">
                            <?php if (!empty($errors['phone'])): ?>
                                <div style="color: var(--vg-danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['phone'][0]) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="alternate_phone">Alternate Phone</label>
                            <input type="tel" id="alternate_phone" name="alternate_phone" class="form-control" value="<?= e($old['alternate_phone'] ?? $customer['alternate_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= e($old['email'] ?? $customer['email'] ?? '') ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <div style="color: var(--vg-danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['email'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="vg-card">
                <div class="card-header">
                    <span class="card-title">03. Address</span>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="address_line">Street / Flat / Landmark</label>
                        <input type="text" id="address_line" name="address_line" class="form-control" value="<?= e($old['address_line'] ?? $customer['address_line'] ?? '') ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1rem;" class="vg-split-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?= e($old['city'] ?? $customer['city'] ?? '') ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="state">State</label>
                            <input type="text" id="state" name="state" class="form-control" value="<?= e($old['state'] ?? $customer['state'] ?? '') ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="postal_code">PIN Code</label>
                            <input type="text" id="postal_code" name="postal_code" class="form-control" value="<?= e($old['postal_code'] ?? $customer['postal_code'] ?? '') ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="country">Country</label>
                            <input type="text" id="country" name="country" class="form-control" value="<?= e($old['country'] ?? $customer['country'] ?? 'India') ?>">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Side Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="vg-card">
                <div class="card-header">
                    <span class="card-title">04. Record Status</span>
                </div>
                <div class="card-body" style="padding: 1.25rem;">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Reference Code</label>
                        <div style="background: var(--vg-pearl-50); padding: 0.65rem; border-radius: 6px; font-weight: 700; color: var(--vg-blue-500);">
                            <code><?= e($customer['customer_code']) ?></code>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="status">Profile Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active" <?= ($old['status'] ?? $customer['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($old['status'] ?? $customer['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0; padding-top: 0.75rem; border-top: 1px solid var(--vg-pearl-200);">
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                            <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1" <?= (!empty($old['marketing_consent']) || !empty($customer['marketing_consent'])) ? 'checked' : '' ?> style="width: 16px; height: 16px;">
                            <span style="font-size: 0.85rem; font-weight: 600;">Marketing Consent</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="vg-card" style="position: sticky; top: 1.5rem;">
                <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <button type="submit" class="btn btn-navy" style="width: 100%;">
                        Save Changes
                    </button>
                    <a href="<?= url('/office/customers/' . $customer['id']) ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
