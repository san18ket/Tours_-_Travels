<?php
/**
 * Shared Email Center Navigation Tabs Component
 * Variables: $activeTab ('overview', 'history', 'settings', 'preview')
 */
$activeTab = $activeTab ?? 'overview';
?>
<div class="email-nav-tabs" style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--vg-pearl-200, #E2E8F0); margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 2px; -webkit-overflow-scrolling: touch;">
    <a href="<?= url('/office/emails') ?>" class="nav-tab-pill <?= $activeTab === 'overview' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.88rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; white-space: nowrap; color: <?= $activeTab === 'overview' ? '#176B5B' : '#64748B' ?>; background: <?= $activeTab === 'overview' ? 'rgba(23, 107, 91, 0.08)' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'overview' ? '#176B5B' : 'transparent' ?>;">
        <i class="fa-solid fa-house" style="color: <?= $activeTab === 'overview' ? '#176B5B' : '#94A3B8' ?>;"></i>
        <span>Overview</span>
    </a>
    <a href="<?= url('/office/emails/history') ?>" class="nav-tab-pill <?= $activeTab === 'history' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.88rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; white-space: nowrap; color: <?= $activeTab === 'history' ? '#176B5B' : '#64748B' ?>; background: <?= $activeTab === 'history' ? 'rgba(23, 107, 91, 0.08)' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'history' ? '#176B5B' : 'transparent' ?>;">
        <i class="fa-solid fa-clock-rotate-left" style="color: <?= $activeTab === 'history' ? '#176B5B' : '#94A3B8' ?>;"></i>
        <span>Email History</span>
    </a>
    <a href="<?= url('/office/emails/preview') ?>" class="nav-tab-pill <?= $activeTab === 'preview' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.88rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; white-space: nowrap; color: <?= $activeTab === 'preview' ? '#176B5B' : '#64748B' ?>; background: <?= $activeTab === 'preview' ? 'rgba(23, 107, 91, 0.08)' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'preview' ? '#176B5B' : 'transparent' ?>;">
        <i class="fa-solid fa-file-invoice" style="color: <?= $activeTab === 'preview' ? '#176B5B' : '#94A3B8' ?>;"></i>
        <span>Email Templates</span>
    </a>
    <a href="<?= url('/office/emails/settings') ?>" class="nav-tab-pill <?= $activeTab === 'settings' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.88rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; white-space: nowrap; color: <?= $activeTab === 'settings' ? '#176B5B' : '#64748B' ?>; background: <?= $activeTab === 'settings' ? 'rgba(23, 107, 91, 0.08)' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'settings' ? '#176B5B' : 'transparent' ?>;">
        <i class="fa-solid fa-gear" style="color: <?= $activeTab === 'settings' ? '#176B5B' : '#94A3B8' ?>;"></i>
        <span>Email Settings</span>
    </a>
</div>
