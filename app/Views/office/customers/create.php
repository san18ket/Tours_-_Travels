<?php
/**
 * VoyageGo — Premium Multi-Step Customer Profile Registration
 * Professional 4-step onboarding wizard:
 * Step 1: Basic Identity
 * Step 2: Contact Information
 * Step 3: Residential & Billing Address
 * Step 4: Review, Record Settings & Final Confirmation
 */
?>

<!-- Breadcrumb -->
<nav class="breadcrumb-container" aria-label="Breadcrumb" style="margin-bottom: 1.25rem;">
    <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: var(--vg-text-muted);">
        <li>
            <a href="<?= url('/office/dashboard') ?>" style="color: var(--vg-blue-500); text-decoration: none; font-weight: 500;">Dashboard</a>
        </li>
        <li style="color: var(--vg-text-muted);">&rsaquo;</li>
        <li>
            <a href="<?= url('/office/customers') ?>" style="color: var(--vg-blue-500); text-decoration: none; font-weight: 500;">Customers</a>
        </li>
        <li style="color: var(--vg-text-muted);">&rsaquo;</li>
        <li style="color: var(--vg-navy-900); font-weight: 600;" aria-current="page">Add Customer</li>
    </ol>
</nav>

<!-- Page Header with Subtle Glassmorphic Background -->
<div class="customer-header-card" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.96) 0%, rgba(30, 41, 59, 0.94) 100%); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: var(--vg-radius-lg); padding: 1.5rem 1.75rem; margin-bottom: 1.75rem; color: #FFF; box-shadow: var(--vg-shadow-md); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="width: 52px; height: 52px; border-radius: 12px; background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
            <svg width="26" height="26" fill="none" stroke="#FFFFFF" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <h1 style="font-size: 1.45rem; font-weight: 800; color: #FFFFFF; margin: 0; letter-spacing: -0.01em;">Register Customer Profile</h1>
                <span style="background: rgba(217, 119, 6, 0.2); border: 1px solid rgba(245, 158, 11, 0.4); color: #FBBF24; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em;">Customer Profile Only</span>
            </div>
            <p style="color: #94A3B8; font-size: 0.88rem; margin: 0.25rem 0 0 0;">Create a verified customer profile for bookings and invoices without office console credentials.</p>
        </div>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('/office/customers') ?>" class="btn" style="background: rgba(255, 255, 255, 0.1); color: #FFF; border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px);">
            &larr; Back to Customers
        </a>
    </div>
</div>

<!-- Duplicate Records Warning Banner (If Triggered by Backend) -->
<?php if (!empty($duplicates)): ?>
<div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: var(--vg-radius-md); padding: 1.25rem 1.5rem; margin-bottom: 1.75rem; box-shadow: var(--vg-shadow-sm);">
    <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
        <div style="font-size: 1.5rem; color: #D97706; line-height: 1;">⚠️</div>
        <div style="flex: 1;">
            <h3 style="font-size: 1rem; font-weight: 700; color: #92400E; margin: 0 0 0.4rem 0;">Potential Matching Customer Record Detected</h3>
            <p style="font-size: 0.88rem; color: #78350F; margin: 0 0 0.85rem 0;">
                The contact phone or email entered matches one or more existing customer records. To avoid accidental duplicates, please review existing accounts:
            </p>
            <div style="background: #FFF; border: 1px solid #FDE68A; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.85rem;">
                <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.88rem; color: var(--vg-navy-900);">
                    <?php foreach ($duplicates as $dup): ?>
                        <li style="margin-bottom: 0.35rem;">
                            <strong><?= e($dup['name']) ?></strong> (<code><?= e($dup['customer_code']) ?></code>) — Phone: <?= e($dup['phone']) ?> <?= !empty($dup['email']) ? '• Email: ' . e($dup['email']) : '' ?> <?= !empty($dup['city']) ? '• City: ' . e($dup['city']) : '' ?>
                            <a href="<?= url('/office/customers/' . $dup['id']) ?>" target="_blank" style="color: var(--vg-blue-500); font-weight: 600; margin-left: 0.5rem; text-decoration: underline;">View Profile &rarr;</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; font-weight: 600; color: #92400E; cursor: pointer;">
                <input type="checkbox" name="confirm_duplicate" value="1" form="createCustomerForm" id="confirmDuplicateCheckbox" style="width: 17px; height: 17px; cursor: pointer;">
                <span>I have reviewed the existing records and confirm this is a distinct customer profile with shared contact information.</span>
            </label>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Multi-Step Wizard Progress Stepper (4 Steps) -->
<div class="vg-card wizard-stepper-card" style="box-shadow: var(--vg-shadow-sm); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); margin-bottom: 1.75rem; padding: 1.25rem 1.5rem; background: #FFFFFF;">
    <!-- Progress percentage bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--vg-navy-900);">
            Registration Progress: <span id="wizardStepLabel" style="color: var(--vg-blue-500);">Step 1 of 4</span>
        </span>
        <span id="wizardPercentLabel" style="font-size: 0.82rem; font-weight: 800; color: var(--vg-blue-500);">25% Complete</span>
    </div>
    
    <div style="height: 6px; background: #E2E8F0; border-radius: 999px; overflow: hidden; margin-bottom: 1.5rem;">
        <div id="wizardProgressBar" style="width: 25%; height: 100%; background: linear-gradient(90deg, #2563EB 0%, #3B82F6 100%); transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 999px;"></div>
    </div>

    <!-- Interactive Step Badges (4 Clean Steps) -->
    <div class="stepper-indicators-container" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; position: relative;">
        <!-- Step 1 Indicator -->
        <button type="button" class="step-indicator-btn active" data-target-step="1" onclick="wizardGoToStep(1)">
            <div class="step-indicator-badge">
                <span class="step-num">1</span>
                <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="step-indicator-text">
                <span class="step-title">Basic Info</span>
                <span class="step-desc">Guest Identity</span>
            </div>
        </button>

        <!-- Step 2 Indicator -->
        <button type="button" class="step-indicator-btn" data-target-step="2" onclick="wizardGoToStep(2)">
            <div class="step-indicator-badge">
                <span class="step-num">2</span>
                <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="step-indicator-text">
                <span class="step-title">Contact</span>
                <span class="step-desc">Mobile &amp; Email</span>
            </div>
        </button>

        <!-- Step 3 Indicator -->
        <button type="button" class="step-indicator-btn" data-target-step="3" onclick="wizardGoToStep(3)">
            <div class="step-indicator-badge">
                <span class="step-num">3</span>
                <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="step-indicator-text">
                <span class="step-title">Address</span>
                <span class="step-desc">Billing Details</span>
            </div>
        </button>

        <!-- Step 4 Indicator (Review & Save) -->
        <button type="button" class="step-indicator-btn" data-target-step="4" onclick="wizardGoToStep(4)">
            <div class="step-indicator-badge">
                <span class="step-num">4</span>
                <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="step-indicator-text">
                <span class="step-title">Review &amp; Save</span>
                <span class="step-desc">Settings &amp; Save</span>
            </div>
        </button>
    </div>
</div>

<!-- Main Multi-Step Form Layout -->
<form action="<?= url('/office/customers') ?>" method="POST" id="createCustomerForm" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="target_entity" value="customer">

    <!-- ========================================================================= -->
    <!-- STEP 1: Basic Information -->
    <!-- ========================================================================= -->
    <div class="wizard-step-panel" id="wizardStepPanel1" data-step="1">
        <div class="vg-card" style="box-shadow: var(--vg-shadow-sm); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md);">
            <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--vg-navy-900); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">01</span>
                    <div>
                        <h2 class="card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">Basic Information</h2>
                        <span style="font-size: 0.8rem; color: var(--vg-text-muted);">Guest legal identity, respectful title and personal details</span>
                    </div>
                </div>
                <span class="step-tag" style="background: #EFF6FF; color: #2563EB; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 12px; border: 1px solid #BFDBFE;">Step 1 of 4</span>
            </div>
            <div class="card-body" style="padding: 1.75rem 1.5rem;">
                
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 1.25rem; margin-bottom: 1.5rem;" class="vg-split-2">
                    <!-- Title -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="title" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Salutation / Title</label>
                        <select id="title" name="title" class="form-select">
                            <option value="" <?= empty($old['title']) ? 'selected' : '' ?>>— None —</option>
                            <option value="Mr" <?= ($old['title'] ?? '') === 'Mr' ? 'selected' : '' ?>>Mr.</option>
                            <option value="Mrs" <?= ($old['title'] ?? '') === 'Mrs' ? 'selected' : '' ?>>Mrs.</option>
                            <option value="Ms" <?= ($old['title'] ?? '') === 'Ms' ? 'selected' : '' ?>>Ms.</option>
                            <option value="Dr" <?= ($old['title'] ?? '') === 'Dr' ? 'selected' : '' ?>>Dr.</option>
                            <option value="Prof" <?= ($old['title'] ?? '') === 'Prof' ? 'selected' : '' ?>>Prof.</option>
                            <option value="Lord" <?= ($old['title'] ?? '') === 'Lord' ? 'selected' : '' ?>>Lord</option>
                            <option value="Lady" <?= ($old['title'] ?? '') === 'Lady' ? 'selected' : '' ?>>Lady</option>
                        </select>
                    </div>

                    <!-- Full Name -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="name" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">
                            Full Legal Name <span style="color: var(--vg-danger); font-weight: 700;">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Vikramaditya Sharma or Countess Genevieve" value="<?= e($old['name'] ?? '') ?>" autocomplete="name">
                        <small style="color: var(--vg-text-muted); font-size: 0.75rem;">As it appears on travel vouchers, invoices, and manifests.</small>
                        <?php if (!empty($errors['name'])): ?>
                            <div style="color: var(--vg-danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['name'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="vg-split-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- Date of Birth -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="date_of_birth" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Date of Birth (Optional)</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?= e($old['date_of_birth'] ?? '') ?>">
                        <small style="color: var(--vg-text-muted); font-size: 0.75rem;">Enables birthday &amp; anniversary special promotions.</small>
                        <?php if (!empty($errors['date_of_birth'])): ?>
                            <div style="color: var(--vg-danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['date_of_birth'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Gender (Inclusive Options) -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="gender" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Gender (Optional)</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="" <?= empty($old['gender']) ? 'selected' : '' ?>>— Unspecified —</option>
                            <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="non_binary" <?= ($old['gender'] ?? '') === 'non_binary' ? 'selected' : '' ?>>Non-Binary</option>
                            <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other / Self-Described</option>
                            <option value="prefer_not_to_say" <?= ($old['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer Not to Say</option>
                        </select>
                        <small style="color: var(--vg-text-muted); font-size: 0.75rem;">Used for respectful chauffeur communication.</small>
                    </div>
                </div>

            </div>
            
            <!-- Step 1 Footer -->
            <div class="card-footer" style="background: var(--vg-pearl-50); border-top: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <a href="<?= url('/office/customers') ?>" class="btn btn-outline" style="color: var(--vg-text-secondary);">Cancel</a>
                <button type="button" class="btn btn-navy" onclick="wizardNextStep(1)" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.4rem;">
                    <span>Next: Contact Details</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STEP 2: Contact Information -->
    <!-- ========================================================================= -->
    <div class="wizard-step-panel" id="wizardStepPanel2" data-step="2" style="display: none;">
        <div class="vg-card" style="box-shadow: var(--vg-shadow-sm); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md);">
            <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--vg-navy-900); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">02</span>
                    <div>
                        <h2 class="card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">Contact Information</h2>
                        <span style="font-size: 0.8rem; color: var(--vg-text-muted);">Direct mobile numbers and guest email address</span>
                    </div>
                </div>
                <span class="step-tag" style="background: #EFF6FF; color: #2563EB; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 12px; border: 1px solid #BFDBFE;">Step 2 of 4</span>
            </div>
            <div class="card-body" style="padding: 1.75rem 1.5rem;">
                
                <div class="vg-split-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                    <!-- Primary Mobile -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="phone" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">
                            Primary Mobile Number <span style="color: var(--vg-danger); font-weight: 700;">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" class="form-control" required placeholder="+91 98111 22334" value="<?= e($old['phone'] ?? $old['primary_mobile'] ?? '') ?>" autocomplete="tel">
                        <small style="color: var(--vg-text-muted); font-size: 0.75rem;">Used for booking reservations and chauffeur coordination.</small>
                        <?php if (!empty($errors['phone'])): ?>
                            <div style="color: var(--vg-danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['phone'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Alternate Phone -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="alternate_phone" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Alternate Phone (Optional)</label>
                        <input type="tel" id="alternate_phone" name="alternate_phone" class="form-control" placeholder="+91 98222 33445" value="<?= e($old['alternate_phone'] ?? '') ?>">
                        <small style="color: var(--vg-text-muted); font-size: 0.75rem;">Emergency line, assistant or second passenger contact.</small>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="email" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Email Address (Optional)</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="client@example.com" value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
                    <small style="color: var(--vg-text-muted); font-size: 0.75rem;">For tax invoice delivery, booking vouchers &amp; receipt PDFs.</small>
                    <?php if (!empty($errors['email'])): ?>
                        <div style="color: var(--vg-danger); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Step 2 Footer -->
            <div class="card-footer" style="background: var(--vg-pearl-50); border-top: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-outline" onclick="wizardGoToStep(1)">&larr; Back: Basic Info</button>
                <button type="button" class="btn btn-navy" onclick="wizardNextStep(2)" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.4rem;">
                    <span>Next: Billing &amp; Residential Address</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STEP 3: Address Details -->
    <!-- ========================================================================= -->
    <div class="wizard-step-panel" id="wizardStepPanel3" data-step="3" style="display: none;">
        <div class="vg-card" style="box-shadow: var(--vg-shadow-sm); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md);">
            <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--vg-navy-900); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">03</span>
                    <div>
                        <h2 class="card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">Residential &amp; Billing Address</h2>
                        <span style="font-size: 0.8rem; color: var(--vg-text-muted);">Physical location for tax invoices, luggage transfers and itineraries</span>
                    </div>
                </div>
                <span class="step-tag" style="background: #EFF6FF; color: #2563EB; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 12px; border: 1px solid #BFDBFE;">Step 3 of 4</span>
            </div>
            <div class="card-body" style="padding: 1.75rem 1.5rem;">
                
                <!-- Address Line -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="address_line" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Street / Suite / Landmark</label>
                    <input type="text" id="address_line" name="address_line" class="form-control" placeholder="e.g. 42 Palm Avenue, Civil Lines or 12 Place Vendôme" value="<?= e($old['address_line'] ?? '') ?>">
                </div>

                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;" class="vg-split-2">
                    <!-- City -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="city" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">City</label>
                        <input type="text" id="city" name="city" class="form-control" placeholder="New Delhi / Paris" value="<?= e($old['city'] ?? '') ?>">
                    </div>

                    <!-- State -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="state" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">State / Province</label>
                        <input type="text" id="state" name="state" class="form-control" placeholder="Delhi / Île-de-France" value="<?= e($old['state'] ?? '') ?>">
                    </div>

                    <!-- Postal Code -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="postal_code" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">PIN / Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" placeholder="110001 / 75001" value="<?= e($old['postal_code'] ?? '') ?>">
                    </div>

                    <!-- Country -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="country" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Country</label>
                        <input type="text" id="country" name="country" class="form-control" value="<?= e($old['country'] ?? 'India') ?>">
                    </div>
                </div>

            </div>

            <!-- Step 3 Footer -->
            <div class="card-footer" style="background: var(--vg-pearl-50); border-top: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-outline" onclick="wizardGoToStep(2)">&larr; Back: Contact</button>
                <button type="button" class="btn btn-navy" onclick="wizardNextStep(3)" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.4rem;">
                    <span>Next: Review &amp; Operational Settings</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STEP 4: Review, Record Settings & Final Confirmation -->
    <!-- ========================================================================= -->
    <div class="wizard-step-panel" id="wizardStepPanel4" data-step="4" style="display: none;">
        <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 1.5rem; align-items: start;" class="customer-form-grid">
            
            <!-- Left: Live Customer Review Dossier -->
            <div class="vg-card" style="box-shadow: var(--vg-shadow-sm); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md);">
                <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="width: 28px; height: 28px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">✓</span>
                        <div>
                            <h2 class="card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">Review Profile Dossier</h2>
                            <span style="font-size: 0.8rem; color: var(--vg-text-muted);">Verify details before registering customer record</span>
                        </div>
                    </div>
                    <span style="font-size: 0.75rem; color: #10B981; font-weight: 700; text-transform: uppercase;">Ready to Register</span>
                </div>
                
                <div class="card-body" style="padding: 1.5rem;">
                    <!-- Dossier Summary Grid -->
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.75rem; margin-bottom: 0.85rem;">
                            <div>
                                <span style="font-size: 0.75rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700; display: block;">Customer Full Name</span>
                                <span id="reviewName" style="font-size: 1.1rem; font-weight: 800; color: var(--vg-navy-900);">—</span>
                            </div>
                            <button type="button" onclick="wizardGoToStep(1)" style="background: none; border: none; color: var(--vg-blue-500); font-size: 0.8rem; font-weight: 600; cursor: pointer; text-decoration: underline;">
                                Edit
                            </button>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.75rem; margin-bottom: 0.85rem;">
                            <div>
                                <span style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700; display: block;">Primary Mobile</span>
                                <span id="reviewPhone" style="font-size: 0.9rem; font-weight: 700; color: var(--vg-navy-900);">—</span>
                            </div>
                            <div>
                                <span style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700; display: block;">Email Address</span>
                                <span id="reviewEmail" style="font-size: 0.85rem; color: var(--vg-navy-900);">—</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <span style="font-size: 0.72rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700; display: block;">Location / City</span>
                                <span id="reviewLocation" style="font-size: 0.85rem; color: var(--vg-navy-900);">—</span>
                            </div>
                            <button type="button" onclick="wizardGoToStep(3)" style="background: none; border: none; color: var(--vg-blue-500); font-size: 0.8rem; font-weight: 600; cursor: pointer; text-decoration: underline;">
                                Edit
                            </button>
                        </div>
                    </div>

                    <!-- Privilege Notice -->
                    <div style="background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 8px; padding: 1rem; font-size: 0.82rem; color: #334155; line-height: 1.45;">
                        <strong style="color: var(--vg-navy-900); display: block; margin-bottom: 0.25rem;">🔒 Account Isolation Guarantee</strong>
                        This registration strictly adds a customer profile to VoyageGo. No staff credentials or administrative access to the office dashboard are generated.
                    </div>
                </div>

                <div class="card-footer" style="background: var(--vg-pearl-50); border-top: 1px solid var(--vg-pearl-200); padding: 1.25rem 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="wizardGoToStep(3)">&larr; Back: Address Details</button>
                </div>
            </div>

            <!-- Right: Operational Settings & Confirmation -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                
                <!-- SECTION 04: Operational Settings & Record Settings -->
                <div class="vg-card" style="box-shadow: var(--vg-shadow-sm); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md);">
                    <div class="card-header" style="background: var(--vg-pearl-50); border-bottom: 1px solid var(--vg-pearl-200); padding: 1rem 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="width: 24px; height: 24px; border-radius: 50%; background: var(--vg-navy-900); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">04</span>
                            <h2 class="card-title" style="font-size: 1rem; font-weight: 700; color: var(--vg-navy-900); margin: 0;">Record Settings</h2>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 1.25rem;">
                        
                        <!-- Reference Code Display -->
                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Customer Reference Code</label>
                            <div style="background: var(--vg-pearl-50); border: 1px solid var(--vg-pearl-200); border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
                                <code style="font-size: 0.95rem; font-weight: 800; color: var(--vg-blue-500);"><?= e($customerCode) ?></code>
                                <span style="font-size: 0.7rem; color: var(--vg-text-muted); text-transform: uppercase; font-weight: 700;">System Generated</span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label class="form-label" for="status" style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900);">Profile Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="active" <?= ($old['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Can Book Fleet &amp; Tours)</option>
                                <option value="inactive" <?= ($old['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive (Dormant Account)</option>
                            </select>
                        </div>

                        <!-- Marketing Consent -->
                        <div class="form-group" style="margin-bottom: 0; padding-top: 0.75rem; border-top: 1px solid var(--vg-pearl-200);">
                            <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer;">
                                <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1" <?= !empty($old['marketing_consent']) ? 'checked' : '' ?> style="margin-top: 0.2rem; width: 16px; height: 16px; cursor: pointer;">
                                <div>
                                    <span style="font-weight: 600; font-size: 0.85rem; color: var(--vg-navy-900); display: block;">Marketing &amp; Itinerary Consent</span>
                                    <span style="font-size: 0.78rem; color: var(--vg-text-muted); display: block; line-height: 1.35;">Explicit consent granted to receive tailored luxury journey alerts.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Action Box -->
                <div class="vg-card" style="box-shadow: var(--vg-shadow-md); border: 1px solid var(--vg-pearl-200); border-radius: var(--vg-radius-md); background: #FFFFFF;">
                    <div class="card-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <button type="submit" class="btn btn-navy" id="btnSubmitCustomer" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.85rem 1rem; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Save Customer Profile</span>
                        </button>
                        <a href="<?= url('/office/customers') ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                            Cancel &amp; Discard
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

</form>

<style>
/* Stepper Indicators Styling */
.step-indicator-btn {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 0.75rem 0.65rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    cursor: pointer;
    text-align: left;
    transition: all 0.25s ease;
    width: 100%;
}
.step-indicator-btn:hover {
    background: #F1F5F9;
    border-color: #CBD5E1;
}
.step-indicator-badge {
    width: 28px;
    height: 28px;
    min-width: 28px;
    border-radius: 50%;
    background: #CBD5E1;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}
.step-check {
    display: none;
    width: 16px;
    height: 16px;
}
.step-indicator-text {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.step-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--vg-navy-900);
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.step-desc {
    font-size: 0.7rem;
    color: var(--vg-text-muted);
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

/* Active Step State */
.step-indicator-btn.active {
    background: #EFF6FF;
    border-color: #2563EB;
    box-shadow: 0 0 0 1px #2563EB;
}
.step-indicator-btn.active .step-indicator-badge {
    background: #2563EB;
    color: #FFFFFF;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
}
.step-indicator-btn.active .step-title {
    color: #1D4ED8;
}

/* Completed Step State */
.step-indicator-btn.completed {
    background: #F0FDF4;
    border-color: #86EFAC;
}
.step-indicator-btn.completed .step-indicator-badge {
    background: #10B981;
    color: #FFFFFF;
}
.step-indicator-btn.completed .step-num {
    display: none;
}
.step-indicator-btn.completed .step-check {
    display: block;
}

/* Animations */
.wizard-step-panel {
    animation: fadeInStep 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeInStep {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustment */
@media (max-width: 900px) {
    .stepper-indicators-container {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .customer-form-grid {
        grid-template-columns: 1fr !important;
    }
}
@media (max-width: 600px) {
    .stepper-indicators-container {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
// Wizard State Management (4 Steps)
let currentStep = 1;
const totalSteps = 4;

function updateWizardProgress(step) {
    currentStep = step;
    const percent = Math.round((step / totalSteps) * 100);
    
    // Progress bar and labels
    const pBar = document.getElementById('wizardProgressBar');
    const pLabel = document.getElementById('wizardPercentLabel');
    const sLabel = document.getElementById('wizardStepLabel');
    
    if (pBar) pBar.style.width = percent + '%';
    if (pLabel) pLabel.textContent = percent + '% Complete';
    if (sLabel) sLabel.textContent = 'Step ' + step + ' of ' + totalSteps;

    // Update Indicators
    const buttons = document.querySelectorAll('.step-indicator-btn');
    buttons.forEach(btn => {
        const btnStep = parseInt(btn.getAttribute('data-target-step'), 10);
        btn.classList.remove('active');
        
        if (btnStep === step) {
            btn.classList.add('active');
            btn.classList.remove('completed');
        } else if (btnStep < step) {
            btn.classList.add('completed');
        } else {
            btn.classList.remove('completed');
        }
    });

    // Populate Step 4 live dossier if arriving on Step 4
    if (step === 4) {
        populateReviewDossier();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalSteps) return;

    // Hide all panels
    for (let i = 1; i <= totalSteps; i++) {
        const panel = document.getElementById('wizardStepPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    // Show target panel
    const targetPanel = document.getElementById('wizardStepPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateWizardProgress(targetStep);

    // Scroll smoothly to top of stepper
    const stepper = document.querySelector('.wizard-stepper-card');
    if (stepper) {
        stepper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function wizardValidateCurrentStep(step) {
    const panel = document.getElementById('wizardStepPanel' + step);
    if (!panel) return true;

    // Check all required inputs/selects in this step panel
    const requiredInputs = panel.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    let firstInvalid = null;

    requiredInputs.forEach(input => {
        input.style.borderColor = '';

        if (!input.checkValidity() || input.value.trim() === '') {
            isValid = false;
            input.style.borderColor = 'var(--vg-danger)';
            if (!firstInvalid) {
                firstInvalid = input;
            }
        }
    });

    if (!isValid && firstInvalid) {
        firstInvalid.focus();
        firstInvalid.reportValidity();
        return false;
    }

    return true;
}

function wizardNextStep(currentStepNum) {
    if (!wizardValidateCurrentStep(currentStepNum)) {
        return;
    }
    wizardGoToStep(currentStepNum + 1);
}

// Live Review Dossier Population
function populateReviewDossier() {
    const titleVal = document.getElementById('title')?.value || '';
    const nameVal = document.getElementById('name')?.value || 'Customer Name';
    const fullName = (titleVal ? titleVal + '. ' : '') + nameVal;
    
    const phoneVal = document.getElementById('phone')?.value || '—';
    const emailVal = document.getElementById('email')?.value || '—';
    
    const cityVal = document.getElementById('city')?.value || '';
    const countryVal = document.getElementById('country')?.value || 'India';
    const locationStr = (cityVal ? cityVal + ', ' : '') + countryVal;

    // Update DOM elements
    const rName = document.getElementById('reviewName');
    const rPhone = document.getElementById('reviewPhone');
    const rEmail = document.getElementById('reviewEmail');
    const rLocation = document.getElementById('reviewLocation');

    if (rName) rName.textContent = fullName;
    if (rPhone) rPhone.textContent = phoneVal;
    if (rEmail) rEmail.textContent = emailVal;
    if (rLocation) rLocation.textContent = locationStr;
}

// Unsaved changes confirmation warning
let isFormDirty = false;
let isFormSubmitting = false;

const customerForm = document.getElementById('createCustomerForm');
if (customerForm) {
    customerForm.addEventListener('input', function() {
        isFormDirty = true;
    });

    customerForm.addEventListener('submit', function() {
        isFormSubmitting = true;
    });
}

window.addEventListener('beforeunload', function(e) {
    if (isFormDirty && !isFormSubmitting) {
        e.preventDefault();
        e.returnValue = 'You have unsaved customer details. Are you sure you want to exit?';
        return e.returnValue;
    }
});

// Server-side errors auto-focus step
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($duplicates) || !empty($errors['phone']) || !empty($errors['email'])): ?>
        wizardGoToStep(2);
    <?php else: ?>
        wizardGoToStep(1);
    <?php endif; ?>
});
</script>
