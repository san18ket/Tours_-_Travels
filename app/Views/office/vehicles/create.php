<?php
/**
 * VoyageGo / MANASWI Tours & Travels — Master Commercial Vehicle Registration Form
 * Professional 4-step wizard matching customers/create architecture & design system
 */
?>

<div class="modern-form-wrapper">

    <!-- Centered Professional 4-Step Stepper Header -->
    <div class="modern-stepper-header-centered">
        <div class="modern-stepper-container">
            <div class="modern-stepper-track">
                <!-- Step 1 -->
                <button type="button" class="modern-step-item active" data-step-target="1" onclick="wizardGoToStep(1)">
                    <span class="modern-step-circle">1</span>
                    <span class="modern-step-label">Vehicle Specifications</span>
                </button>
                <div class="modern-step-connector" id="stepConn1"></div>

                <!-- Step 2 -->
                <button type="button" class="modern-step-item" data-step-target="2" onclick="wizardGoToStep(2)">
                    <span class="modern-step-circle">2</span>
                    <span class="modern-step-label">Operations &amp; Odometer</span>
                </button>
                <div class="modern-step-connector" id="stepConn2"></div>

                <!-- Step 3 -->
                <button type="button" class="modern-step-item" data-step-target="3" onclick="wizardGoToStep(3)">
                    <span class="modern-step-circle">3</span>
                    <span class="modern-step-label">Documents &amp; Compliance</span>
                </button>
                <div class="modern-step-connector" id="stepConn3"></div>

                <!-- Step 4 -->
                <button type="button" class="modern-step-item" data-step-target="4" onclick="wizardGoToStep(4)">
                    <span class="modern-step-circle">4</span>
                    <span class="modern-step-label">Review &amp; Save</span>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <script>
            (function() {
                var firstErr = <?= json_encode((is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: __('form.please_review_errors')) ?>;
                var emit = function() {
                    if (typeof window.vgNotify === 'function') {
                        window.vgNotify(firstErr, 'error');
                    } else {
                        setTimeout(emit, 40);
                    }
                };
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', emit);
                } else {
                    emit();
                }
            })();
        </script>
    <?php endif; ?>

    <!-- Master Form Container -->
    <form action="<?= url('/office/vehicles') ?>" method="POST" id="createVehicleForm" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <!-- Default status saved as available -->
        <input type="hidden" name="status" value="available">

        <div class="modern-form-card">

            <!-- ========================================================================= -->
            <!-- STEP 1: Vehicle Specifications & Fleet Photo                              -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel1">

                <!-- Professional Vehicle Photo Upload Component (Exact Customer Avatar Architecture) -->
                <div class="customer-avatar-upload-card">
                    <div class="customer-avatar-preview-wrap" id="avatarDropZone" onclick="document.getElementById('vehicle_photo').click()" title="Click to upload vehicle photo">
                        <input type="file" id="vehicle_photo" name="vehicle_photo" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;" onchange="handleVehiclePhotoChange(this)" onclick="event.stopPropagation()">
                        <input type="hidden" name="vehicle_photo_base64" id="vehicle_photo_base64" value="">
                        <div class="avatar-default-icon" id="avatarDefaultIcon">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <img id="avatarPreviewImg" src="" alt="Vehicle Photo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <div class="avatar-camera-badge" title="Upload Photo">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                    </div>
                    <div class="customer-avatar-meta">
                        <div class="customer-avatar-title-row">
                            <span class="customer-avatar-label">Vehicle Fleet Photo</span>
                            <span class="modern-badge-subtle">Optional</span>
                        </div>
                        <p class="customer-avatar-help">Upload a clear exterior front-quarter or side photo of the vehicle. Supported: JPG, PNG, WEBP (Max 5MB).</p>
                        <div class="customer-avatar-actions">
                            <button type="button" class="customer-avatar-btn-upload" onclick="document.getElementById('vehicle_photo').click()">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Photo
                            </button>
                            <button type="button" class="customer-avatar-btn-remove" id="btnRemoveAvatar" style="display: none;" onclick="clearVehiclePhoto()">
                                <i class="fa-solid fa-trash-can"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Registration Number & Make / Model -->
                <div class="modern-form-grid-2">
                    <!-- Registration Number -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="vehicle_number">
                            Registration Number <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-id-card"></i></span>
                            <input type="text" id="vehicle_number" name="vehicle_number" class="modern-input-pill" required value="<?= e($old['vehicle_number'] ?? '') ?>" placeholder="e.g. MH 12 AB 1234" style="text-transform: uppercase; font-family: monospace; font-weight: 700; letter-spacing: 0.5px;" oninput="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['vehicle_number'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['vehicle_number'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Make & Model -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="make_model">
                            Make &amp; Model <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-car"></i></span>
                            <input type="text" id="make_model" name="make_model" class="modern-input-pill" required value="<?= e($old['make_model'] ?? '') ?>" placeholder="e.g. Toyota Innova Crysta ZX" oninput="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['make_model'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['make_model'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Row 2: Seating Capacity & Fuel Type -->
                <div class="modern-form-grid-2">
                    <!-- Seating Capacity -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="seating_capacity">
                            Seating Capacity (Passengers) <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-users"></i></span>
                            <input type="number" id="seating_capacity" name="seating_capacity" class="modern-input-pill" required value="<?= e($old['seating_capacity'] ?? '7') ?>" min="1" max="60" placeholder="e.g. 7" oninput="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['seating_capacity'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['seating_capacity'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Fuel Type -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="fuel_type">
                            Fuel Type <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-gas-pump"></i></span>
                            <select id="fuel_type" name="fuel_type" class="modern-select-pill" required onchange="syncReviewData()">
                                <option value="diesel" <?= ($old['fuel_type'] ?? 'diesel') === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="petrol" <?= ($old['fuel_type'] ?? '') === 'petrol' ? 'selected' : '' ?>>Petrol</option>
                                <option value="cng" <?= ($old['fuel_type'] ?? '') === 'cng' ? 'selected' : '' ?>>CNG</option>
                                <option value="electric" <?= ($old['fuel_type'] ?? '') === 'electric' ? 'selected' : '' ?>>Electric (EV)</option>
                            </select>
                        </div>
                        <?php if (!empty($errors['fuel_type'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['fuel_type'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <a href="<?= url('/office/vehicles') ?>" class="modern-btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(1)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 2: Operations, Odometer & Expiry Dates                               -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel2" style="display: none;">
                <div class="modern-form-grid-3">
                    <!-- Initial Odometer -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="odometer_km">
                            Initial Odometer (KM) <span class="req">*</span>
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-gauge-high"></i></span>
                            <input type="number" id="odometer_km" name="odometer_km" class="modern-input-pill" required value="<?= e($old['odometer_km'] ?? '0') ?>" min="0" placeholder="e.g. 15000" oninput="syncReviewData()">
                        </div>
                        <?php if (!empty($errors['odometer_km'])): ?>
                            <div class="modern-field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['odometer_km'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Registration Expiry -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="registration_expiry">
                            Registration Expiry (RC)
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                            <input type="date" id="registration_expiry" name="registration_expiry" class="modern-input-pill" value="<?= e($old['registration_expiry'] ?? date('Y-m-d', strtotime('+1 year'))) ?>" onchange="syncReviewData()">
                        </div>
                    </div>

                    <!-- Insurance Expiry -->
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="insurance_expiry">
                            Comprehensive Insurance Expiry
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-shield-heart"></i></span>
                            <input type="date" id="insurance_expiry" name="insurance_expiry" class="modern-input-pill" value="<?= e($old['insurance_expiry'] ?? date('Y-m-d', strtotime('+1 year'))) ?>" onchange="syncReviewData()">
                        </div>
                    </div>
                </div>

                <!-- Operational Readiness Callout Banner -->
                <div class="operational-readiness-box">
                    <div class="readiness-icon-circle">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="readiness-content">
                        <div class="readiness-header-row">
                            <span class="readiness-title">Fleet Deployment Status</span>
                            <span class="readiness-badge"><i class="fa-solid fa-circle-check"></i> Available for Dispatch</span>
                        </div>
                        <p class="readiness-desc">Upon registration, this vehicle will be initialized into active inventory with an <strong>Available</strong> status, immediately eligible for scheduling, tour assignment, and driver dispatches.</p>
                    </div>
                </div>

                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(1)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(2)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 3: Documents & Compliance Upload                                     -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel3" style="display: none;">
                <div class="modern-form-grid-2">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="document_type">
                            Document Category / Type
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-folder-closed"></i></span>
                            <select id="document_type" name="document_type" class="modern-select-pill" onchange="onDocumentTypeChange(this.value)">
                                <option value="rc_book" selected>RC Book (Registration Certificate)</option>
                                <option value="insurance_policy">Comprehensive Insurance Policy</option>
                                <option value="tourist_permit">All India Tourist Permit (AITP)</option>
                                <option value="fitness_certificate">Fitness Certificate (FC)</option>
                                <option value="puc">Pollution Under Control (PUC)</option>
                                <option value="other">Other Fleet Commercial Document</option>
                            </select>
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label" for="compliance_notes">
                            Compliance Reference / Notes
                        </label>
                        <div class="modern-input-wrapper">
                            <span class="modern-input-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                            <input type="text" id="compliance_notes" name="compliance_notes" class="modern-input-pill" placeholder="e.g. RTO Pune Commercial Registered, Fastag Linked" oninput="syncReviewData()">
                        </div>
                    </div>
                </div>

                <!-- DYNAMIC PROFESSIONAL DOCUMENT UPLOAD BOX (Matching customers/create) -->
                <div class="document-upload-container">
                    <div class="document-upload-header">
                        <label class="modern-form-label" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-cloud-arrow-up" style="color: #176B5B;"></i>
                            <span id="docUploadBoxTitle">Upload RC Book Scan / Document</span>
                        </label>
                        <span id="docTypeBadge" class="modern-badge doc-category-badge">
                            RTO Registration Document
                        </span>
                    </div>

                    <!-- Modern Interactive Dropzone -->
                    <div class="document-dropzone" id="documentDropzone" onclick="document.getElementById('rc_document_file').click()">
                        <input type="file" id="rc_document_file" name="rc_document_file" accept="image/png,image/jpeg,image/jpg,image/webp,application/pdf" style="display: none;" onchange="handleDocumentFileChange(this)" onclick="event.stopPropagation()">
                        <input type="hidden" name="rc_document_file_base64" id="rc_document_file_base64" value="">
                        <input type="hidden" name="rc_document_file_name" id="rc_document_file_name" value="">

                        <!-- Empty State Inside Dropzone -->
                        <div id="dropzoneEmptyState">
                            <div class="document-dropzone-halo">
                                <i class="fa-solid fa-file-invoice" id="docDropzoneIcon"></i>
                            </div>
                            <div class="document-dropzone-prompt">
                                <strong>Click to upload</strong> or drag and drop your document scan
                            </div>
                            <div class="document-dropzone-sub" id="docDropzoneHelp">
                                Supported formats: PNG, JPG, JPEG, WEBP or PDF (Max 10MB)
                            </div>
                        </div>

                        <!-- Populated / Preview State Inside Dropzone -->
                        <div id="dropzonePreviewState" style="display: none;">
                            <div class="doc-preview-wrapper" onclick="event.stopPropagation()">
                                <div class="doc-preview-thumb-box" onclick="openDocPreviewModal()" style="cursor: pointer;" title="Click to view full preview">
                                    <img id="docPreviewImage" src="" alt="Document preview" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    <div id="docPreviewPdfIcon" style="display: none; font-size: 2.2rem; color: #EF4444;">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="doc-thumb-hover-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                                </div>
                                <div class="doc-preview-meta">
                                    <div class="doc-preview-name" id="docPreviewName">rc_scan.jpg</div>
                                    <div class="doc-preview-size" id="docPreviewSize">0 KB</div>
                                    <div class="doc-preview-status">
                                        <i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Document Attached &amp; Ready
                                    </div>
                                </div>
                                <div class="doc-preview-actions">
                                    <button type="button" class="btn btn-sm btn-doc-view-preview" onclick="openDocPreviewModal()">
                                        <i class="fa-solid fa-eye"></i> Preview
                                    </button>
                                    <button type="button" class="btn btn-sm btn-doc-change" onclick="document.getElementById('rc_document_file').click()">
                                        <i class="fa-solid fa-arrows-rotate"></i> Change
                                    </button>
                                    <button type="button" class="btn btn-sm btn-doc-remove" onclick="clearDocumentUpload()">
                                        <i class="fa-solid fa-trash-can"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(2)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="modern-btn-next" onclick="wizardNextStep(3)">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 4: Review & Save                                                     -->
            <!-- ========================================================================= -->
            <div class="modern-step-panel" id="wizardPanel4" style="display: none;">

                <!-- Live Executive Dossier Container (Matching customers/create) -->
                <div class="review-dossier-wrapper">

                    <!-- Vehicle Hero Banner -->
                    <div class="review-hero-banner">
                        <div class="review-avatar-container">
                            <div class="review-avatar-monogram" id="sumAvatarMonogram">
                                <i class="fa-solid fa-car" style="font-size: 2.2rem; color: #176B5B;"></i>
                            </div>
                            <img id="sumAvatarImg" src="" alt="Vehicle Photo" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>

                        <div class="review-hero-meta">
                            <div class="review-hero-title-row">
                                <h3 class="review-hero-name" id="sumHeroName">—</h3>
                                <span class="review-code-badge">
                                    <i class="fa-solid fa-id-card"></i>
                                    <code id="sumHeroPlate">—</code>
                                </span>
                            </div>
                            <div class="review-hero-tags">
                                <span class="review-tag-item">
                                    <i class="fa-solid fa-users"></i> <span id="sumTagCapacity">7 Seater</span>
                                </span>
                                <span class="review-tag-item">
                                    <i class="fa-solid fa-gas-pump"></i> <span id="sumTagFuel">Diesel</span>
                                </span>
                                <span class="review-tag-item">
                                    <i class="fa-solid fa-gauge-high"></i> <span id="sumTagOdo">0 KM</span>
                                </span>
                                <span class="review-status-pill is-active">
                                    <i class="fa-solid fa-circle-check"></i> Available for Dispatch
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 3 Comprehensive Information Section Cards -->
                    <div class="review-sections-grid">

                        <!-- Card 1: Vehicle Specifications -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-car-side"></i> Vehicle Specifications</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(1)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Registration Plate</span>
                                    <span class="review-value font-mono font-bold highlight-teal" id="sumCardPlate">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Make &amp; Model</span>
                                    <span class="review-value font-bold" id="sumCardMakeModel">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Seating Capacity</span>
                                    <span class="review-value" id="sumCardCapacity">7 Passengers</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Fuel Type</span>
                                    <span class="review-value capitalize" id="sumCardFuel">Diesel</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Operations & Compliance -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-gauge-high"></i> Operations &amp; Compliance</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(2)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Initial Odometer</span>
                                    <span class="review-value font-mono font-bold" id="sumCardOdo">0 KM</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Registration Expiry (RC)</span>
                                    <span class="review-value" id="sumCardRcExpiry">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Insurance Expiry</span>
                                    <span class="review-value" id="sumCardInsuranceExpiry">—</span>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Deployment Status</span>
                                    <span class="review-value font-bold" style="color: #059669;">
                                        <i class="fa-solid fa-circle-check"></i> Available for Dispatch
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Attached Documentation -->
                        <div class="review-section-card">
                            <div class="review-card-header">
                                <div><i class="fa-solid fa-file-shield"></i> Attached Documentation</div>
                                <button type="button" class="review-edit-jump-btn" onclick="wizardGoToStep(3)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                            </div>
                            <div class="review-card-rows">
                                <div class="review-info-row">
                                    <span class="review-label">Document Category</span>
                                    <span class="review-value font-semibold" id="sumCardDocType">RC Book (Registration Certificate)</span>
                                </div>
                                <div class="review-info-row review-doc-row" style="flex-direction: column; align-items: stretch; gap: 0.45rem;">
                                    <span class="review-label">Attached Document Preview</span>
                                    <div id="sumCardDocFile" style="width: 100%;">
                                        <span class="review-doc-badge empty"><i class="fa-solid fa-circle-xmark"></i> Not attached (Optional)</span>
                                    </div>
                                </div>
                                <div class="review-info-row">
                                    <span class="review-label">Compliance Notes</span>
                                    <span class="review-value" id="sumCardNotes">—</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="modern-form-actions">
                    <button type="button" class="modern-btn-prev" onclick="wizardGoToStep(3)">
                        <i class="fa-solid fa-arrow-left"></i> Previous
                    </button>
                    <button type="submit" class="modern-btn-submit" id="btnSubmitVehicle">
                        <i class="fa-solid fa-check"></i> <span id="btnSubmitVehicleText">Register Commercial Vehicle</span>
                    </button>
                </div>
            </div>
        </div>

    </form>

    <!-- Professional Document Lightbox Preview Modal (Exact customers/create implementation) -->
    <div id="docPreviewModal" class="doc-lightbox-backdrop" style="display: none;" onclick="closeDocPreviewModal(event)">
        <div class="doc-lightbox-container" onclick="event.stopPropagation()">
            <div class="doc-lightbox-header">
                <div class="doc-lightbox-title-box">
                    <i class="fa-solid fa-file-shield doc-lightbox-icon"></i>
                    <div>
                        <h3 class="doc-lightbox-title" id="docLightboxTitle">Document Preview</h3>
                        <p class="doc-lightbox-subtitle" id="docLightboxSubtitle">Fleet Verification Attachment</p>
                    </div>
                </div>
                <button type="button" class="doc-lightbox-close" onclick="closeDocPreviewModal()" title="Close preview (Esc)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="doc-lightbox-body">
                <img id="docLightboxImg" src="" alt="Document Full Preview" style="display: none; max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px;">
                <div id="docLightboxPdf" style="display: none; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1rem; text-align: center; gap: 1rem;">
                    <i class="fa-solid fa-file-pdf" style="font-size: 4.5rem; color: #EF4444;"></i>
                    <h4 style="font-size: 1.15rem; color: #0C2B52; margin: 0; font-weight: 700;" id="docLightboxPdfName">document.pdf</h4>
                    <p style="color: #64748B; font-size: 0.9rem; max-width: 380px; margin: 0;">PDF documents cannot be displayed directly in image view. Open or download to inspect full contents.</p>
                    <a id="docLightboxPdfOpenBtn" href="#" target="_blank" class="btn" style="background: #176B5B; color: #FFF; padding: 0.55rem 1.4rem; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open PDF in New Tab
                    </a>
                </div>
            </div>
            <div class="doc-lightbox-footer">
                <span class="doc-lightbox-hint"><i class="fa-solid fa-circle-info"></i> VoyageGo Fleet Compliance Archive</span>
                <a id="docLightboxDownloadBtn" href="#" download="document" class="btn btn-sm" style="background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF; font-weight: 700; border-radius: 8px; padding: 0.4rem 1rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-download"></i> Download File
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Wizard State & Navigation (4-Step Streamlined Process matching customers/create)
let currentStep = 1;
const totalSteps = 4;
let photoDataUrl = null;
let uploadedDocumentMeta = null;
let uploadedDocumentDataUrl = null;

function updateStepperUI(activeStep) {
    currentStep = activeStep;

    // Update Step items
    const stepButtons = document.querySelectorAll('.modern-step-item');
    stepButtons.forEach((btn, index) => {
        const stepNum = index + 1;
        btn.classList.remove('active', 'completed');
        if (stepNum === activeStep) {
            btn.classList.add('active');
        } else if (stepNum < activeStep) {
            btn.classList.add('completed');
        }
    });

    // Update connector lines
    for (let i = 1; i < totalSteps; i++) {
        const conn = document.getElementById('stepConn' + i);
        if (conn) {
            if (i < activeStep) {
                conn.classList.add('completed');
            } else {
                conn.classList.remove('completed');
            }
        }
    }

    // When on step 4, populate the comprehensive live summary dossier
    if (activeStep === 4) {
        populateSummaryDossier();
    }
}

function wizardGoToStep(targetStep) {
    targetStep = parseInt(targetStep, 10);
    if (targetStep < 1 || targetStep > totalSteps) return;

    // Hide all panels
    for (let i = 1; i <= totalSteps; i++) {
        const panel = document.getElementById('wizardPanel' + i);
        if (panel) panel.style.display = 'none';
    }

    // Show target panel
    const targetPanel = document.getElementById('wizardPanel' + targetStep);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    updateStepperUI(targetStep);

    // Scroll to top smoothly
    const wrapper = document.querySelector('.modern-form-wrapper');
    if (wrapper) {
        wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function wizardValidateCurrentStep(stepNum) {
    const panel = document.getElementById('wizardPanel' + stepNum);
    if (!panel) return true;

    if (window.VoyageValidation && typeof window.VoyageValidation.validateContainer === 'function') {
        const isValid = window.VoyageValidation.validateContainer(panel);
        if (!isValid) {
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Please fill in all required fields marked with *', 'error');
            }
            return false;
        }
        return true;
    }

    const requiredFields = panel.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    let firstInvalid = null;

    requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
            isValid = false;
            field.style.borderColor = '#EF4444';
            field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.15)';
            if (!firstInvalid) firstInvalid = field;

            const onInput = function() {
                if (field.value.trim() !== '') {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                    field.removeEventListener('input', onInput);
                    field.removeEventListener('change', onInput);
                }
            };
            field.addEventListener('input', onInput);
            field.addEventListener('change', onInput);
        } else {
            field.style.borderColor = '';
            field.style.boxShadow = '';
        }
    });

    if (!isValid && firstInvalid) {
        firstInvalid.focus();
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Please fill in all required fields marked with *', 'error');
        }
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

// Live Summary Dossier Synchronizer for Step 4
function populateSummaryDossier() {
    const plate = (document.getElementById('vehicle_number')?.value || '').trim().toUpperCase();
    const makeModel = (document.getElementById('make_model')?.value || '').trim();
    const capacity = (document.getElementById('seating_capacity')?.value || '7').trim();
    const fuelSelect = document.getElementById('fuel_type');
    const fuelText = fuelSelect ? fuelSelect.options[fuelSelect.selectedIndex]?.text || 'Diesel' : 'Diesel';
    const odo = (document.getElementById('odometer_km')?.value || '0').trim();
    const rcExp = document.getElementById('registration_expiry')?.value || '';
    const insExp = document.getElementById('insurance_expiry')?.value || '';
    const docTypeSelect = document.getElementById('document_type');
    const docTypeText = docTypeSelect ? docTypeSelect.options[docTypeSelect.selectedIndex]?.text || 'RC Book' : 'RC Book';
    const notes = (document.getElementById('compliance_notes')?.value || '').trim();

    // Hero banner
    const heroNameEl = document.getElementById('sumHeroName');
    if (heroNameEl) heroNameEl.textContent = makeModel || 'Vehicle Specifications';

    const heroPlateEl = document.getElementById('sumHeroPlate');
    if (heroPlateEl) heroPlateEl.textContent = plate || 'MH-XX-XX-XXXX';

    const tagCapEl = document.getElementById('sumTagCapacity');
    if (tagCapEl) tagCapEl.textContent = capacity + ' Seater';

    const tagFuelEl = document.getElementById('sumTagFuel');
    if (tagFuelEl) tagFuelEl.textContent = fuelText;

    const tagOdoEl = document.getElementById('sumTagOdo');
    if (tagOdoEl) tagOdoEl.textContent = parseInt(odo || 0, 10).toLocaleString('en-IN') + ' KM';

    // Hero vehicle photo / avatar
    const heroMonogram = document.getElementById('sumAvatarMonogram');
    const heroImg = document.getElementById('sumAvatarImg');
    if (photoDataUrl) {
        if (heroImg) {
            heroImg.src = photoDataUrl;
            heroImg.style.display = 'block';
        }
        if (heroMonogram) heroMonogram.style.display = 'none';
    } else {
        if (heroImg) heroImg.style.display = 'none';
        if (heroMonogram) heroMonogram.style.display = 'flex';
    }

    // Card 1: Specifications
    const cardPlateEl = document.getElementById('sumCardPlate');
    if (cardPlateEl) cardPlateEl.textContent = plate || '—';

    const cardMakeModelEl = document.getElementById('sumCardMakeModel');
    if (cardMakeModelEl) cardMakeModelEl.textContent = makeModel || '—';

    const cardCapEl = document.getElementById('sumCardCapacity');
    if (cardCapEl) cardCapEl.textContent = capacity ? (capacity + ' Passengers') : '—';

    const cardFuelEl = document.getElementById('sumCardFuel');
    if (cardFuelEl) cardFuelEl.textContent = fuelText || '—';

    // Card 2: Operations & Compliance
    const cardOdoEl = document.getElementById('sumCardOdo');
    if (cardOdoEl) cardOdoEl.textContent = parseInt(odo || 0, 10).toLocaleString('en-IN') + ' KM';

    const cardRcExpEl = document.getElementById('sumCardRcExpiry');
    if (cardRcExpEl) cardRcExpEl.textContent = rcExp ? formatDisplayDate(rcExp) : 'Not specified';

    const cardInsExpEl = document.getElementById('sumCardInsuranceExpiry');
    if (cardInsExpEl) cardInsExpEl.textContent = insExp ? formatDisplayDate(insExp) : 'Not specified';

    // Card 3: Documents & Notes
    const cardDocTypeEl = document.getElementById('sumCardDocType');
    if (cardDocTypeEl) cardDocTypeEl.textContent = docTypeText;

    const cardDocFileEl = document.getElementById('sumCardDocFile');
    if (cardDocFileEl) {
        if (uploadedDocumentMeta) {
            const isPdf = uploadedDocumentMeta.type === 'application/pdf';
            const iconHtml = isPdf ? '<i class="fa-solid fa-file-pdf" style="color: #EF4444; font-size: 1.25rem;"></i>' : '<i class="fa-solid fa-file-image" style="color: #10B981; font-size: 1.25rem;"></i>';
            cardDocFileEl.innerHTML = `
                <div class="review-doc-card-preview" onclick="openDocPreviewModal()" style="cursor: pointer;" title="Click to view full preview">
                    <div class="review-doc-card-icon">${iconHtml}</div>
                    <div class="review-doc-card-meta">
                        <span class="review-doc-filename">${escapeHtml(uploadedDocumentMeta.name)}</span>
                        <span class="review-doc-filesize">${uploadedDocumentMeta.size} &bull; Ready for upload</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-doc-view-preview" style="margin-left: auto;">
                        <i class="fa-solid fa-eye"></i> View
                    </button>
                </div>
            `;
        } else {
            cardDocFileEl.innerHTML = '<span class="review-doc-badge empty"><i class="fa-solid fa-circle-xmark"></i> Not attached (Optional)</span>';
        }
    }

    const cardNotesEl = document.getElementById('sumCardNotes');
    if (cardNotesEl) cardNotesEl.textContent = notes || 'No special fleet remarks';
}

function syncReviewData() {
    if (currentStep === 4) {
        populateSummaryDossier();
    }
}

function formatDisplayDate(dStr) {
    if (!dStr) return '—';
    try {
        const parts = dStr.split('-');
        if (parts.length === 3) {
            const d = new Date(parts[0], parts[1] - 1, parts[2]);
            return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    } catch (e) {}
    return dStr;
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

// =========================================================================
// Vehicle Fleet Photo Upload Handlers
// =========================================================================
function handleVehiclePhotoChange(input) {
    const file = input.files && input.files[0];
    if (!file) return;

    if (!file.type.match(/^image\/(png|jpe?g|webp)$/i)) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Please upload a valid image file (PNG, JPG, or WEBP).', 'error');
        }
        input.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Image size exceeds 5MB limit. Please upload a smaller file.', 'error');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        photoDataUrl = e.target.result;
        const previewImg = document.getElementById('avatarPreviewImg');
        const defaultIcon = document.getElementById('avatarDefaultIcon');
        const removeBtn = document.getElementById('btnRemoveAvatar');
        const b64Input = document.getElementById('vehicle_photo_base64');

        if (previewImg) {
            previewImg.src = photoDataUrl;
            previewImg.style.display = 'block';
        }
        if (defaultIcon) defaultIcon.style.display = 'none';
        if (removeBtn) removeBtn.style.display = 'inline-flex';
        if (b64Input) b64Input.value = photoDataUrl;

        syncReviewData();

        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Vehicle photo attached successfully!', 'success');
        }
    };
    reader.readAsDataURL(file);
}

function clearVehiclePhoto() {
    const input = document.getElementById('vehicle_photo');
    if (input) input.value = '';
    const b64Input = document.getElementById('vehicle_photo_base64');
    if (b64Input) b64Input.value = '';
    photoDataUrl = null;

    const previewImg = document.getElementById('avatarPreviewImg');
    const defaultIcon = document.getElementById('avatarDefaultIcon');
    const removeBtn = document.getElementById('btnRemoveAvatar');

    if (previewImg) {
        previewImg.src = '';
        previewImg.style.display = 'none';
    }
    if (defaultIcon) defaultIcon.style.display = 'flex';
    if (removeBtn) removeBtn.style.display = 'none';

    syncReviewData();
}

// =========================================================================
// Document Type Dynamic Switching & Upload Handlers
// =========================================================================
const vehicleDocConfig = {
    rc_book: {
        title: 'Upload RC Book Scan / Document',
        badge: 'RTO Registration Certificate',
        icon: 'fa-solid fa-file-invoice'
    },
    insurance_policy: {
        title: 'Upload Comprehensive Insurance Policy',
        badge: 'Annual Commercial Coverage',
        icon: 'fa-solid fa-shield-heart'
    },
    tourist_permit: {
        title: 'Upload Tourist Permit (AITP)',
        badge: 'Interstate / All India Permit',
        icon: 'fa-solid fa-passport'
    },
    fitness_certificate: {
        title: 'Upload Fitness Certificate (FC)',
        badge: 'RTO Commercial Fitness',
        icon: 'fa-solid fa-certificate'
    },
    puc: {
        title: 'Upload Pollution Certificate (PUC)',
        badge: 'Emission Compliance',
        icon: 'fa-solid fa-smog'
    },
    other: {
        title: 'Upload Fleet Commercial Document',
        badge: 'Fleet Compliance Attachment',
        icon: 'fa-solid fa-folder-open'
    }
};

function onDocumentTypeChange(val) {
    const cfg = vehicleDocConfig[val] || vehicleDocConfig.other;
    const titleEl = document.getElementById('docUploadBoxTitle');
    const badgeEl = document.getElementById('docTypeBadge');
    const iconEl = document.getElementById('docDropzoneIcon');

    if (titleEl) titleEl.textContent = cfg.title;
    if (badgeEl) badgeEl.textContent = cfg.badge;
    if (iconEl) iconEl.className = cfg.icon;

    syncReviewData();
}

function handleDocumentFileChange(input) {
    const file = input.files && input.files[0];
    if (!file) return;

    const allowedMimes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'application/pdf'];
    if (!allowedMimes.includes(file.type)) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Supported formats are PNG, JPG, JPEG, WEBP or PDF.', 'error');
        }
        input.value = '';
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Document size exceeds 10MB limit. Please upload a smaller file.', 'error');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        uploadedDocumentDataUrl = e.target.result;
        uploadedDocumentMeta = {
            name: file.name,
            size: formatFileSize(file.size),
            type: file.type
        };

        const b64Input = document.getElementById('rc_document_file_base64');
        if (b64Input) b64Input.value = uploadedDocumentDataUrl;

        const nameInput = document.getElementById('rc_document_file_name');
        if (nameInput) nameInput.value = file.name;

        renderDocumentDropzonePreview(file, uploadedDocumentDataUrl);
        syncReviewData();

        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Document attached successfully!', 'success');
        }
    };
    reader.readAsDataURL(file);
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    else return (bytes / 1048576).toFixed(1) + ' MB';
}

function renderDocumentDropzonePreview(file, dataUrl) {
    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const imgEl = document.getElementById('docPreviewImage');
    const pdfIcon = document.getElementById('docPreviewPdfIcon');
    const nameEl = document.getElementById('docPreviewName');
    const sizeEl = document.getElementById('docPreviewSize');

    if (nameEl) nameEl.textContent = file.name;
    if (sizeEl) sizeEl.textContent = formatFileSize(file.size);

    if (file.type === 'application/pdf') {
        if (imgEl) imgEl.style.display = 'none';
        if (pdfIcon) pdfIcon.style.display = 'block';
    } else {
        if (pdfIcon) pdfIcon.style.display = 'none';
        if (imgEl) {
            imgEl.src = dataUrl;
            imgEl.style.display = 'block';
        }
    }

    if (emptyState) emptyState.style.display = 'none';
    if (previewState) previewState.style.display = 'block';
}

function clearDocumentUpload() {
    const input = document.getElementById('rc_document_file');
    if (input) input.value = '';
    const b64Input = document.getElementById('rc_document_file_base64');
    if (b64Input) b64Input.value = '';
    const nameInput = document.getElementById('rc_document_file_name');
    if (nameInput) nameInput.value = '';
    uploadedDocumentMeta = null;
    uploadedDocumentDataUrl = null;

    const emptyState = document.getElementById('dropzoneEmptyState');
    const previewState = document.getElementById('dropzonePreviewState');
    const imgEl = document.getElementById('docPreviewImage');

    if (imgEl) imgEl.src = '';
    if (emptyState) emptyState.style.display = 'block';
    if (previewState) previewState.style.display = 'none';

    syncReviewData();
}

function openDocPreviewModal() {
    if (!uploadedDocumentDataUrl && !uploadedDocumentMeta) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('No document uploaded to preview', 'info');
        }
        return;
    }
    const modal = document.getElementById('docPreviewModal');
    const imgEl = document.getElementById('docLightboxImg');
    const pdfEl = document.getElementById('docLightboxPdf');
    const titleEl = document.getElementById('docLightboxTitle');
    const subEl = document.getElementById('docLightboxSubtitle');
    const dlBtn = document.getElementById('docLightboxDownloadBtn');
    const pdfOpenBtn = document.getElementById('docLightboxPdfOpenBtn');
    const pdfNameEl = document.getElementById('docLightboxPdfName');

    const fileName = uploadedDocumentMeta ? uploadedDocumentMeta.name : 'vehicle_document_preview';
    const fileSize = uploadedDocumentMeta ? uploadedDocumentMeta.size : '';
    const fileType = uploadedDocumentMeta ? uploadedDocumentMeta.type : '';

    if (titleEl) titleEl.textContent = fileName;
    if (subEl) subEl.textContent = (fileSize ? fileSize + ' • ' : '') + 'Fleet Verification Attachment';
    if (dlBtn) {
        dlBtn.href = uploadedDocumentDataUrl || '#';
        dlBtn.download = fileName;
    }

    if (fileType && fileType.startsWith('image/')) {
        if (imgEl) {
            imgEl.src = uploadedDocumentDataUrl;
            imgEl.style.display = 'block';
        }
        if (pdfEl) pdfEl.style.display = 'none';
    } else {
        if (imgEl) imgEl.style.display = 'none';
        if (pdfEl) pdfEl.style.display = 'flex';
        if (pdfNameEl) pdfNameEl.textContent = fileName;
        if (pdfOpenBtn) pdfOpenBtn.href = uploadedDocumentDataUrl || '#';
    }

    if (modal) modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDocPreviewModal(e) {
    if (e && e.target && e.target !== e.currentTarget && !e.target.closest('.doc-lightbox-close')) {
        return;
    }
    const modal = document.getElementById('docPreviewModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDocPreviewModal();
    }
});

// Drag & Drop Event Registration & Initializers
document.addEventListener('DOMContentLoaded', function() {
    wizardGoToStep(1);

    // Photo drag & drop
    const avatarDrop = document.getElementById('avatarDropZone');
    if (avatarDrop) {
        ['dragenter', 'dragover'].forEach(eventName => {
            avatarDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                avatarDrop.style.borderColor = '#10B981';
                avatarDrop.style.transform = 'scale(1.05)';
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            avatarDrop.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                avatarDrop.style.borderColor = '#176B5B';
                avatarDrop.style.transform = '';
            });
        });
        avatarDrop.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                const input = document.getElementById('vehicle_photo');
                if (input) {
                    input.files = files;
                    handleVehiclePhotoChange(input);
                }
            }
        });
    }

    // Document Dropzone drag & drop
    const dropzone = document.getElementById('documentDropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('is-dragover');
            });
        });
        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                const input = document.getElementById('rc_document_file');
                if (input) {
                    input.files = files;
                    handleDocumentFileChange(input);
                }
            }
        });
    }

    const initialDocType = document.getElementById('document_type');
    if (initialDocType) {
        onDocumentTypeChange(initialDocType.value);
    }

    // Form submission feedback
    const vehForm = document.getElementById('createVehicleForm');
    const submitBtn = document.getElementById('btnSubmitVehicle');
    if (vehForm && submitBtn) {
        vehForm.addEventListener('submit', function() {
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.85';
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Registering Commercial Vehicle...</span>';
        });
    }
});
</script>

<style>
/* ========================================================================= */
/* Top Utility Bar                                                           */
/* ========================================================================= */
.customer-create-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    padding: 0 0.5rem;
}
.create-topbar-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #475569;
    padding: 8px 18px;
    border-radius: 9999px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.create-topbar-back-btn:hover {
    background: #F8FAFC;
    color: #176B5B;
    border-color: #176B5B;
    transform: translateX(-2px);
}
.create-topbar-admin-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%);
    color: #FFFFFF;
    padding: 8px 18px;
    border-radius: 9999px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(23, 107, 91, 0.25);
}
.create-topbar-admin-btn:hover {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: #FFFFFF;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

@media (max-width: 768px) {
    .customer-create-top-bar {
        margin-bottom: 0.85rem;
        gap: 0.5rem;
    }
    .create-topbar-back-btn, .create-topbar-admin-btn {
        padding: 6px 14px;
        font-size: 0.8rem;
    }
}

/* ========================================================================= */
/* Centered Modern Stepper Header                                            */
/* ========================================================================= */
.modern-stepper-header-centered {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 2rem;
    padding: 0;
    width: 100%;
}
.modern-stepper-container {
    width: 100%;
    max-width: 100%;
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    padding: 1.1rem 2rem;
    box-shadow: 0 4px 20px rgba(12, 43, 82, 0.04);
}
.modern-stepper-track {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    width: 100%;
    max-width: 960px;
    margin: 0 auto;
}
.modern-step-item {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer;
    padding: 0.35rem 0.6rem !important;
    border-radius: 12px !important;
    transition: all 0.2s ease !important;
    z-index: 2;
    color: #64748B !important;
    outline: none !important;
    text-decoration: none !important;
}
.modern-step-item:hover {
    color: #0C2B52 !important;
    transform: translateY(-1px);
}
.modern-step-circle {
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    border-radius: 50% !important;
    background: #FFFFFF !important;
    color: #64748B !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    border: 2px solid #CBD5E1 !important;
    margin-bottom: 0.45rem !important;
    transition: all 0.25s ease !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
}
.modern-step-label {
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    color: #64748B !important;
    margin-top: 0 !important;
    white-space: nowrap !important;
    transition: color 0.2s ease !important;
}
.modern-step-item.active .modern-step-circle {
    background: #176B5B !important;
    border-color: #176B5B !important;
    color: #FFFFFF !important;
    box-shadow: 0 0 0 4px rgba(23, 107, 91, 0.18), 0 4px 12px rgba(23, 107, 91, 0.25) !important;
    transform: scale(1.08) !important;
}
.modern-step-item.active .modern-step-label {
    color: #176B5B !important;
    font-weight: 700 !important;
}
.modern-step-item.completed .modern-step-circle {
    background: #10B981 !important;
    border-color: #10B981 !important;
    color: #FFFFFF !important;
}
.modern-step-item.completed .modern-step-label {
    color: #10B981 !important;
}
.modern-step-connector {
    flex: 1 !important;
    height: 3px !important;
    background: #E2E8F0 !important;
    margin: -1.2rem 0.5rem 0 0.5rem !important;
    transition: background 0.3s ease !important;
    border-radius: 4px !important;
}
.modern-step-connector.completed {
    background: #10B981 !important;
}

@media (max-width: 768px) {
    .modern-stepper-header-centered {
        margin-bottom: 1.5rem;
    }
    .modern-stepper-container {
        padding: 0.85rem 1rem;
        border-radius: 18px;
    }
    .modern-step-circle {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        font-size: 0.88rem !important;
        margin-bottom: 0.35rem !important;
    }
    .modern-step-label {
        font-size: 0.76rem !important;
    }
    .modern-step-connector {
        margin: -1.1rem 0.35rem 0 0.35rem !important;
    }
}

@media (max-width: 480px) {
    .modern-stepper-container {
        padding: 0.75rem 0.65rem;
        border-radius: 16px;
    }
    .modern-step-item {
        padding: 0.2rem 0.35rem !important;
    }
    .modern-step-circle {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        font-size: 0.82rem !important;
        margin-bottom: 0.25rem !important;
    }
    .modern-step-connector {
        margin: -0.95rem 0.25rem 0 0.25rem !important;
    }
    .modern-step-item:not(.active) .modern-step-label {
        display: none !important;
    }
    .modern-step-item.active .modern-step-label {
        display: inline-block !important;
        font-size: 0.72rem !important;
    }
}

/* ========================================================================= */
/* Form Cards & Section Headers                                             */
/* ========================================================================= */
.modern-form-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0.25rem 0 3.5rem 0;
}
.modern-form-card {
    width: 100%;
    max-width: 100%;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    padding: 2rem 2.25rem;
    box-shadow: 0 6px 24px rgba(15, 23, 42, 0.05);
}
@media (max-width: 768px) {
    .modern-form-wrapper {
        padding: 0 0 2rem;
    }
    .modern-form-card {
        padding: 1.5rem 1.25rem;
        border-radius: 16px;
    }
}

.modern-section-header {
    display: flex;
    align-items: center;
    gap: 1.15rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #F1F5F9;
}
.modern-section-badge {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: #E8F4F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.modern-section-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0 0 0.2rem 0;
    letter-spacing: -0.3px;
}
.modern-section-sub {
    font-size: 0.88rem;
    color: #64748B;
    margin: 0;
}

/* ========================================================================= */
/* Photo Upload Component (Exact Customers Avatar)                          */
/* ========================================================================= */
.customer-avatar-upload-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, #F8FAFD 0%, #F1F6FA 100%);
    border: 1.5px solid #DCE6F2;
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 14px rgba(11, 25, 44, 0.03);
}
.customer-avatar-preview-wrap {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    border: 3px solid #176B5B;
    background: #E8F4F0;
    position: relative;
    cursor: pointer;
    overflow: visible;
    flex-shrink: 0;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.customer-avatar-preview-wrap:hover {
    transform: scale(1.04);
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.25);
}
.avatar-default-icon {
    font-size: 2.2rem;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.avatar-camera-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #176B5B;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    border: 2px solid #FFFFFF;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
}
.customer-avatar-meta {
    flex: 1;
}
.customer-avatar-title-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 0.25rem;
}
.customer-avatar-label {
    font-size: 1rem;
    font-weight: 700;
    color: #0C2B52;
}
.modern-badge-subtle {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    background: #E2E8F0;
    color: #475569;
    padding: 2px 8px;
    border-radius: 50px;
}
.customer-avatar-help {
    font-size: 0.82rem;
    color: #64748B;
    margin: 0 0 0.75rem 0;
    line-height: 1.4;
}
.customer-avatar-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.customer-avatar-btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.customer-avatar-btn-upload:hover {
    background: #10B981;
}
.customer-avatar-btn-remove {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FECACA;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.customer-avatar-btn-remove:hover {
    background: #FCA5A5;
}

@media (max-width: 640px) {
    .customer-avatar-upload-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
        padding: 1.25rem;
    }
    .customer-avatar-title-row {
        justify-content: center;
    }
    .customer-avatar-actions {
        justify-content: center;
    }
}

/* ========================================================================= */
/* Grid Layouts & Input Styling                                              */
/* ========================================================================= */
.modern-form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.35rem 1.5rem;
    margin-bottom: 1.35rem;
}
.modern-form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.35rem 1.5rem;
    margin-bottom: 1.35rem;
}
@media (max-width: 860px) {
    .modern-form-grid-3 {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 680px) {
    .modern-form-grid-2 {
        grid-template-columns: 1fr;
    }
}

.modern-form-group {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}
.modern-form-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0C2B52;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.req {
    color: #EF4444;
    font-weight: 800;
}

.modern-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.modern-input-icon {
    position: absolute;
    left: 14px;
    color: #94A3B8;
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease;
    z-index: 1;
}
.modern-input-pill,
.modern-select-pill {
    width: 100%;
    padding: 10px 14px 10px 42px;
    background: #F8FAFC;
    border: 1.5px solid #CBD5E1;
    border-radius: 12px;
    font-size: 0.92rem;
    color: #0C2B52;
    font-weight: 500;
    transition: all 0.2s ease;
    outline: none;
}
.modern-input-pill:focus,
.modern-select-pill:focus {
    background: #FFFFFF;
    border-color: #176B5B;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.15);
}
.modern-input-pill:focus + .modern-input-icon,
.modern-input-wrapper:focus-within .modern-input-icon {
    color: #176B5B;
}
.modern-field-error {
    font-size: 0.78rem;
    color: #EF4444;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.2rem;
}

/* Operational Readiness Callout */
.operational-readiness-box {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    background: linear-gradient(135deg, #F0FDF4 0%, #E8F8F0 100%);
    border: 1.5px solid #BBF7D0;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin: 1.5rem 0;
}
.readiness-icon-circle {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #10B981;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.readiness-content {
    flex: 1;
}
.readiness-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.4rem;
    flex-wrap: wrap;
}
.readiness-title {
    font-size: 0.98rem;
    font-weight: 800;
    color: #065F46;
}
.readiness-badge {
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.readiness-desc {
    font-size: 0.85rem;
    color: #047857;
    margin: 0;
    line-height: 1.45;
}

/* ========================================================================= */
/* Document Upload Container & Dropzone (Matching customers/create)         */
/* ========================================================================= */
.document-upload-container {
    margin-top: 1rem;
    margin-bottom: 1.5rem;
}
.document-upload-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.doc-category-badge {
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 50px;
}
.document-dropzone {
    border: 2px dashed #CBD5E1;
    border-radius: 16px;
    background: #F8FAFC;
    padding: 2.25rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
}
.document-dropzone:hover,
.document-dropzone.is-dragover {
    border-color: #176B5B;
    background: #F0FDF8;
}
.document-dropzone-halo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #E8F4F0;
    color: #176B5B;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin: 0 auto 0.85rem auto;
    transition: all 0.2s ease;
}
.document-dropzone:hover .document-dropzone-halo {
    transform: scale(1.1);
    background: #176B5B;
    color: #FFFFFF;
}
.document-dropzone-prompt {
    font-size: 0.95rem;
    color: #1E293B;
    margin-bottom: 0.35rem;
}
.document-dropzone-prompt strong {
    color: #176B5B;
}
.document-dropzone-sub {
    font-size: 0.8rem;
    color: #64748B;
}

/* Populated preview state */
.doc-preview-wrapper {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: #FFFFFF;
    border: 1.5px solid #DCE6F2;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    text-align: left;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
}
.doc-preview-thumb-box {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.doc-thumb-hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(12, 43, 82, 0.55);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.doc-preview-thumb-box:hover .doc-thumb-hover-overlay {
    opacity: 1;
}
.doc-preview-meta {
    flex: 1;
    overflow: hidden;
}
.doc-preview-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0C2B52;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.2rem;
}
.doc-preview-size {
    font-size: 0.78rem;
    color: #64748B;
    margin-bottom: 0.25rem;
}
.doc-preview-status {
    font-size: 0.8rem;
    font-weight: 600;
    color: #059669;
    display: flex;
    align-items: center;
    gap: 5px;
}
.doc-preview-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.btn-doc-view-preview {
    background: #176B5B;
    color: #FFFFFF;
    border: none;
    font-weight: 700;
    border-radius: 8px;
    padding: 0.4rem 0.85rem;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.btn-doc-view-preview:hover {
    background: #10B981;
}
.btn-doc-change {
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    font-weight: 700;
    border-radius: 8px;
    padding: 0.4rem 0.85rem;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.btn-doc-remove {
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FECACA;
    font-weight: 700;
    border-radius: 8px;
    padding: 0.4rem 0.85rem;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}

@media (max-width: 640px) {
    .doc-preview-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }
    .doc-preview-actions {
        width: 100%;
        justify-content: flex-start;
    }
}

/* ========================================================================= */
/* Step 4: Review & Save Executive Dossier                                   */
/* ========================================================================= */
.review-dossier-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Hero banner */
.review-hero-banner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, #0C2B52 0%, #176B5B 100%);
    border-radius: 18px;
    padding: 1.5rem 1.75rem;
    color: #FFFFFF;
    box-shadow: 0 8px 24px rgba(12, 43, 82, 0.15);
}
.review-avatar-container {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.8);
    background: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
}
.review-avatar-monogram {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: #E8F4F0;
}
.review-hero-meta {
    flex: 1;
}
.review-hero-title-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}
.review-hero-name {
    font-size: 1.45rem;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.3px;
}
.review-code-badge {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #FFFFFF;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.review-code-badge code {
    font-family: monospace;
    font-weight: 800;
    letter-spacing: 0.5px;
}
.review-hero-tags {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}
.review-tag-item {
    background: rgba(255, 255, 255, 0.12);
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #E2E8F0;
}
.review-status-pill {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.review-status-pill.is-active {
    background: #10B981;
    color: #FFFFFF;
}

@media (max-width: 640px) {
    .review-hero-banner {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.25rem;
    }
    .review-hero-title-row {
        justify-content: center;
    }
    .review-hero-tags {
        justify-content: center;
    }
}

/* 3 Section Cards Grid */
.review-sections-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
@media (max-width: 900px) {
    .review-sections-grid {
        grid-template-columns: 1fr;
    }
}

.review-section-card {
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    padding: 1.25rem 1.35rem;
    display: flex;
    flex-direction: column;
}
.review-card-header {
    font-size: 0.95rem;
    font-weight: 800;
    color: #0C2B52;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.85rem;
    margin-bottom: 0.85rem;
    border-bottom: 1px solid #E2E8F0;
}
.review-card-header div {
    display: flex;
    align-items: center;
    gap: 8px;
}
.review-card-header i {
    color: #176B5B;
}
.review-edit-jump-btn {
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
    border-radius: 6px;
    padding: 3px 8px;
    font-size: 0.72rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
}
.review-edit-jump-btn:hover {
    background: #176B5B;
    color: #FFFFFF;
}
.review-card-rows {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    font-size: 0.85rem;
}
.review-info-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.75rem;
}
.review-label {
    color: #64748B;
    font-weight: 600;
    font-size: 0.8rem;
}
.review-value {
    color: #0C2B52;
    font-weight: 600;
    text-align: right;
}
.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }
.font-mono { font-family: monospace; }
.capitalize { text-transform: capitalize; }
.highlight-teal { color: #176B5B; }

.review-doc-badge.empty {
    background: #F1F5F9;
    border: 1px dashed #CBD5E1;
    color: #64748B;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.8rem;
    display: block;
    text-align: center;
}
.review-doc-card-preview {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 10px;
    padding: 8px 12px;
    width: 100%;
}
.review-doc-card-icon {
    flex-shrink: 0;
}
.review-doc-card-meta {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.review-doc-filename {
    font-size: 0.82rem;
    font-weight: 700;
    color: #0C2B52;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.review-doc-filesize {
    font-size: 0.72rem;
    color: #64748B;
}

/* ========================================================================= */
/* Modern Form Action Buttons                                                */
/* ========================================================================= */
.modern-form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #F1F5F9;
    gap: 1rem;
}
.modern-btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #64748B;
    padding: 10px 22px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}
.modern-btn-cancel:hover {
    background: #F8FAFC;
    color: #0C2B52;
    border-color: #94A3B8;
}
.modern-btn-prev {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #475569;
    padding: 10px 22px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.modern-btn-prev:hover {
    background: #F8FAFC;
    color: #176B5B;
    border-color: #176B5B;
}
.modern-btn-next {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #176B5B;
    border: 1.5px solid #176B5B;
    color: #FFFFFF;
    padding: 10px 26px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-left: auto;
}
.modern-btn-next:hover {
    background: #10B981;
    border-color: #10B981;
    transform: translateX(2px);
}
.modern-btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    border: none;
    color: #FFFFFF;
    padding: 11px 28px;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    margin-left: auto;
}
.modern-btn-submit:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
}

@media (max-width: 640px) {
    .modern-form-actions {
        flex-wrap: wrap;
    }
    .modern-btn-cancel, .modern-btn-prev, .modern-btn-next, .modern-btn-submit {
        width: 100%;
        justify-content: center;
    }
    .modern-btn-next, .modern-btn-submit {
        margin-left: 0;
    }
}

/* ========================================================================= */
/* Lightbox Modal for Document Previews                                      */
/* ========================================================================= */
.doc-lightbox-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(12, 43, 82, 0.75);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
.doc-lightbox-container {
    background: #FFFFFF;
    border-radius: 18px;
    max-width: 780px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalPop {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.doc-lightbox-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid #E2E8F0;
    background: #F8FAFC;
}
.doc-lightbox-title-box {
    display: flex;
    align-items: center;
    gap: 12px;
}
.doc-lightbox-icon {
    font-size: 1.4rem;
    color: #176B5B;
}
.doc-lightbox-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0C2B52;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 450px;
}
.doc-lightbox-subtitle {
    font-size: 0.78rem;
    color: #64748B;
    margin: 0;
}
.doc-lightbox-close {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    color: #64748B;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.doc-lightbox-close:hover {
    background: #FEE2E2;
    color: #B91C1C;
    border-color: #FECACA;
}
.doc-lightbox-body {
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F1F5F9;
    min-height: 250px;
}
.doc-lightbox-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1.5rem;
    border-top: 1px solid #E2E8F0;
    background: #F8FAFC;
}
.doc-lightbox-hint {
    font-size: 0.78rem;
    color: #64748B;
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>
