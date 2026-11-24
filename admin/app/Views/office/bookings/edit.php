<div class="modern-form-wrapper" style="max-width: 900px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="margin-bottom: 1.75rem;">
        <?= render_page_header([
            'title'       => 'Edit Reservation ' . e($booking['booking_code']),
            'subtitle'    => 'Update trip category, itinerary schedule, route locations, and guest preferences.',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('sidebar.bookings', 'Bookings'), 'url' => url('/office/bookings')],
                ['label' => e($booking['booking_code']), 'url' => url('/office/bookings/' . $booking['id'])],
                ['label' => 'Edit', 'url' => ''],
            ],
            'actions'     => '<a href="' . url('/office/bookings/' . $booking['id']) . '" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Details</a>',
        ]) ?>
    </div>

    <?php if (!empty($errors)): ?>
        <script>
            (function() {
                var firstErr = <?= json_encode((is_array(reset($errors)) ? reset($errors)[0] : reset($errors)) ?: 'Please review highlighted form errors.') ?>;
                if (typeof window.vgNotify === 'function') {
                    window.vgNotify(firstErr, 'error');
                }
            })();
        </script>
    <?php endif; ?>

    <!-- Master Edit Form -->
    <form action="<?= url('/office/bookings/' . $booking['id']) ?>" method="POST" id="editBookingForm" novalidate>
        <?= csrf_field() ?>

        <div class="modern-form-card" style="background: #ffffff; border-radius: 20px; border: 1.5px solid #E2E8F0; padding: 2rem; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05); margin-bottom: 2rem;">
            <!-- Header: Trip Classification -->
            <div class="modern-section-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.75rem; padding-bottom: 1.25rem; border-bottom: 1px solid #F1F5F9;">
                <div class="modern-section-badge" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(23,107,91,0.12); color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa-solid fa-tag"></i>
                </div>
                <div>
                    <h2 class="modern-section-title" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0F172A;"><?= e(__('booking.trip_information', 'Trip Information')) ?></h2>
                    <p class="modern-section-sub" style="margin: 0.25rem 0 0; font-size: 0.88rem; color: #64748B;">Select the purpose and primary classification for this trip.</p>
                </div>
            </div>

            <!-- Field 1: Trip Category -->
            <div class="modern-form-group" style="margin-bottom: 1.75rem;">
                <label class="modern-form-label" for="trip_category_id" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                    Trip Category <span class="req" style="color: #ef4444;">*</span>
                </label>
                <div class="modern-input-wrapper" style="position: relative;">
                    <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B; pointer-events: none; z-index: 2;">
                        <i class="fa-solid fa-tags"></i>
                    </span>
                    <select id="trip_category_id" name="trip_category_id" class="modern-input-pill" required style="width: 100%; height: 50px; border-radius: 9999px; border: 1.5px solid #CBD5E1; padding: 0 1.25rem 0 2.85rem; font-size: 0.95rem; font-weight: 600; color: #0F172A; cursor: pointer; appearance: auto; -webkit-appearance: auto;">
                        <option value=""><?= e(__('booking.select_trip_category', '-- Select Trip Category --')) ?></option>
                        <?php 
                            $currentCatId = (int)($booking['trip_category_id'] ?? 0);
                            if (isset($_POST['trip_category_id'])) {
                                $currentCatId = (int)$_POST['trip_category_id'];
                            }
                        ?>
                        <?php foreach ($tripCategories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($currentCatId === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?><?= ($cat['status'] === 'inactive') ? ' (Inactive)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($errors['trip_category_id'])): ?>
                    <div class="modern-field-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.4rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['trip_category_id'][0]) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Header: Route & Timetable -->
            <div class="modern-section-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.75rem; padding-bottom: 1.25rem; border-bottom: 1px solid #F1F5F9;">
                <div class="modern-section-badge" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(30,58,138,0.12); color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fa-solid fa-route"></i>
                </div>
                <div>
                    <h2 class="modern-section-title" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0F172A;"><?= e(__('booking.itinerary_schedule', 'Route & Journey Schedule')) ?></h2>
                    <p class="modern-section-sub" style="margin: 0.25rem 0 0; font-size: 0.88rem; color: #64748B;">Origin pickup, final destination, timetable, and passenger count.</p>
                </div>
            </div>

            <!-- Route Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                <div class="modern-form-group">
                    <label class="modern-form-label" for="pickup_address" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                        Pickup Location <span class="req" style="color: #ef4444;">*</span>
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B; pointer-events: none;">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <input type="text" id="pickup_address" name="pickup_address" class="modern-input-pill" required
                               value="<?= e($_POST['pickup_address'] ?? $booking['pickup_address'] ?? '') ?>"
                               style="width: 100%; height: 50px; border-radius: 9999px; border: 1.5px solid #CBD5E1; padding: 0 1.25rem 0 2.85rem; font-size: 0.95rem; font-weight: 500; color: #0F172A;">
                    </div>
                </div>

                <div class="modern-form-group">
                    <label class="modern-form-label" for="drop_address" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                        Drop Location <span class="req" style="color: #ef4444;">*</span>
                    </label>
                    <div class="modern-input-wrapper" style="position: relative;">
                        <span class="modern-input-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748B; pointer-events: none;">
                            <i class="fa-solid fa-map-pin"></i>
                        </span>
                        <input type="text" id="drop_address" name="drop_address" class="modern-input-pill" required
                               value="<?= e($_POST['drop_address'] ?? $booking['drop_address'] ?? '') ?>"
                               style="width: 100%; height: 50px; border-radius: 9999px; border: 1.5px solid #CBD5E1; padding: 0 1.25rem 0 2.85rem; font-size: 0.95rem; font-weight: 500; color: #0F172A;">
                    </div>
                </div>
            </div>

            <!-- Dates Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                <div class="modern-form-group">
                    <label class="modern-form-label" for="start_date" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                        Departure Date <span class="req" style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="start_date" name="start_date" class="modern-input-pill" required
                           value="<?= e($_POST['start_date'] ?? $booking['start_date'] ?? '') ?>"
                           style="width: 100%; height: 50px; border-radius: 9999px; border: 1.5px solid #CBD5E1; padding: 0 1.25rem; font-size: 0.95rem; font-weight: 500; color: #0F172A;">
                </div>

                <div class="modern-form-group">
                    <label class="modern-form-label" for="end_date" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                        Return Date <span class="req" style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="end_date" name="end_date" class="modern-input-pill" required
                           value="<?= e($_POST['end_date'] ?? $booking['end_date'] ?? '') ?>"
                           style="width: 100%; height: 50px; border-radius: 9999px; border: 1.5px solid #CBD5E1; padding: 0 1.25rem; font-size: 0.95rem; font-weight: 500; color: #0F172A;">
                </div>

                <div class="modern-form-group">
                    <label class="modern-form-label" for="pickup_time" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                        Pickup Time
                    </label>
                    <input type="time" id="pickup_time" name="pickup_time" class="modern-input-pill"
                           value="<?= e($_POST['pickup_time'] ?? $booking['pickup_time'] ?? '09:00:00') ?>"
                           style="width: 100%; height: 50px; border-radius: 9999px; border: 1.5px solid #CBD5E1; padding: 0 1.25rem; font-size: 0.95rem; font-weight: 500; color: #0F172A;">
                </div>

                <div class="modern-form-group">
                    <label class="modern-form-label" for="passenger_count" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                        Passengers <span class="req" style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" id="passenger_count" name="passenger_count" min="1" max="150" class="modern-input-pill" required
                           value="<?= (int)($_POST['passenger_count'] ?? $booking['passenger_count'] ?? 1) ?>"
                           style="width: 100%; height: 50px; border-radius: 9999px; border: 1.5px solid #CBD5E1; padding: 0 1.25rem; font-size: 0.95rem; font-weight: 500; color: #0F172A;">
                </div>
            </div>

            <!-- Special Requests -->
            <div class="modern-form-group" style="margin-bottom: 1.5rem;">
                <label class="modern-form-label" for="special_requests" style="display: block; font-size: 0.88rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                    Special Guest Requests &amp; Driver Notes
                </label>
                <textarea id="special_requests" name="special_requests" rows="3" class="modern-input-pill"
                          placeholder="<?= e(__('placeholder.e_g_flight_flight_number_lugga', 'e.g. Flight flight number, luggage count, specific route preference...')) ?>"
                          style="width: 100%; height: auto; border-radius: 14px; border: 1.5px solid #CBD5E1; padding: 0.85rem 1.15rem; font-size: 0.95rem; font-weight: 400; color: #0F172A;"><?= e($_POST['special_requests'] ?? $booking['special_requests'] ?? '') ?></textarea>
            </div>

            <!-- Submit Button Bar -->
            <div style="display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #F1F5F9; padding-top: 1.5rem; margin-top: 1rem;">
                <a href="<?= url('/office/bookings/' . $booking['id']) ?>" class="btn btn-outline" style="height: 48px; border-radius: 9999px; padding: 0 1.5rem; display: inline-flex; align-items: center; justify-content: center; font-weight: 700;">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" style="height: 48px; border-radius: 9999px; padding: 0 2rem; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; gap: 0.5rem;">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

