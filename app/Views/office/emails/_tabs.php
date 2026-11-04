<?php
/**
 * Shared Email Management Navigation Tabs Component
 * Variables: $activeTab ('overview', 'history', 'settings', 'preview')
 */
$activeTab = $activeTab ?? 'overview';
?>
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #2E5077; margin-bottom: 1.75rem; overflow-x: auto; padding-bottom: 2px;">
    <a href="<?= url('/office/emails') ?>" class="nav-tab-pill <?= $activeTab === 'overview' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; color: <?= $activeTab === 'overview' ? '#FFFFFF' : '#94A3B8' ?>; background: <?= $activeTab === 'overview' ? '#1E3E62' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'overview' ? '#10B981' : 'transparent' ?>;">
        <i class="fa-solid fa-chart-pie" style="color: <?= $activeTab === 'overview' ? '#10B981' : '#64748B' ?>;"></i>
        <span>Overview</span>
    </a>
    <a href="<?= url('/office/emails/history') ?>" class="nav-tab-pill <?= $activeTab === 'history' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; color: <?= $activeTab === 'history' ? '#FFFFFF' : '#94A3B8' ?>; background: <?= $activeTab === 'history' ? '#1E3E62' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'history' ? '#10B981' : 'transparent' ?>;">
        <i class="fa-solid fa-list-check" style="color: <?= $activeTab === 'history' ? '#10B981' : '#64748B' ?>;"></i>
        <span>Email History &amp; Retry</span>
    </a>
    <a href="<?= url('/office/emails/settings') ?>" class="nav-tab-pill <?= $activeTab === 'settings' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; color: <?= $activeTab === 'settings' ? '#FFFFFF' : '#94A3B8' ?>; background: <?= $activeTab === 'settings' ? '#1E3E62' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'settings' ? '#10B981' : 'transparent' ?>;">
        <i class="fa-solid fa-sliders" style="color: <?= $activeTab === 'settings' ? '#10B981' : '#64748B' ?>;"></i>
        <span>SMTP Settings &amp; Diagnostics</span>
    </a>
    <a href="<?= url('/office/emails/preview') ?>" class="nav-tab-pill <?= $activeTab === 'preview' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 8px 8px 0 0; transition: all 0.2s; color: <?= $activeTab === 'preview' ? '#FFFFFF' : '#94A3B8' ?>; background: <?= $activeTab === 'preview' ? '#1E3E62' : 'transparent' ?>; border-bottom: 3px solid <?= $activeTab === 'preview' ? '#10B981' : 'transparent' ?>;">
        <i class="fa-solid fa-eye" style="color: <?= $activeTab === 'preview' ? '#10B981' : '#64748B' ?>;"></i>
        <span>Template Previews</span>
    </a>
</div>

