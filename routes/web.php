<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyPhotoController;
use App\Http\Controllers\PropertyFichaController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\BrokerCompanyController;
use App\Http\Controllers\ReferrerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\EmailSettingsController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EmailAssetController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\BrokerManagementController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\EasyBrokerSettingsController;
use App\Http\Controllers\Admin\IntegrationSettingsController;
use App\Http\Controllers\Admin\AutomationController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\SegmentController;
use App\Http\Controllers\Admin\AutomationEngineController;
use App\Http\Controllers\Admin\LeadScoringController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\HelpCenterController;
use App\Http\Controllers\Admin\ContractTemplateController;
use App\Http\Controllers\Admin\ChecklistTemplateController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\RentalProcessController;
use App\Http\Controllers\RentalDocumentController;
use App\Http\Controllers\PolizaJuridicaController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ContentCalendarController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ContactSubmissionController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\FooterController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\ClientEmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\LegalController;
use App\Http\Controllers\Admin\ServiciosPageController;
use App\Http\Controllers\Admin\NosotrosPageController;
use App\Http\Controllers\Admin\VenderPageController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalRentalController;
use App\Http\Controllers\Portal\PortalDocumentController;
use App\Http\Controllers\Admin\PropertyQrController;

// Página pública
Route::get('/', [HomeController::class, 'index'])->name('home');

// Propiedades públicas
Route::get('/propiedades', [PublicController::class, 'propiedades'])->name('propiedades.index');
Route::get('/propiedades/{property}/ficha.pdf', [PropertyFichaController::class, 'pdf'])->name('properties.pdf.public');
Route::get('/propiedades/{id}/{slug?}', [PublicController::class, 'propiedadShow'])->name('propiedades.show');

// Páginas estáticas públicas
Route::get('/nosotros', [PublicController::class, 'nosotros'])->name('nosotros');
Route::get('/servicios', [PublicController::class, 'servicios'])->name('servicios');
Route::get('/contacto', [PublicController::class, 'contacto'])->name('contacto');
Route::post('/contacto', [PublicController::class, 'contactoStore'])->middleware('throttle:public-form')->name('contacto.store');
Route::get('/testimonios', [PublicController::class, 'testimonios'])->name('testimonios');
Route::post('/newsletter/subscribe', [PublicController::class, 'newsletterSubscribe'])->middleware('throttle:newsletter')->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [PublicController::class, 'newsletterUnsubscribe'])->name('newsletter.unsubscribe');

// Blog público
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/p/{slug}', [BlogController::class, 'page'])->name('page.show');

// ===== OBSERVATORIO DE MERCADO (público) =====
Route::prefix('mercado')->name('mercado.')->group(function () {
    // opinion-de-valor DEBE ir ANTES del wildcard /{zona}
    Route::get('/opinion-de-valor',  [\App\Http\Controllers\MarketController::class, 'opinionForm'])->name('opinion');
    Route::post('/opinion-de-valor', [\App\Http\Controllers\ValuationLeadController::class, 'store'])->middleware('throttle:public-form')->name('opinion.store');

    Route::get('/',               [\App\Http\Controllers\MarketController::class, 'index'])->name('index');
    Route::get('/{zona}',         [\App\Http\Controllers\MarketController::class, 'zone'])->name('zone');
    Route::get('/{zona}/{colonia}',[\App\Http\Controllers\MarketController::class, 'colonia'])->name('colonia');
});

// Formularios públicos
Route::get('/form/{slug}', [PublicFormController::class, 'show'])->name('form.show');
Route::post('/form/{slug}', [PublicFormController::class, 'submit'])->middleware('throttle:public-form')->name('form.submit');

// Email open tracking (public, no auth)
Route::get('/track/{trackingId}.gif', [ClientEmailController::class, 'track'])->name('email.track');

// Landing pages (campañas de conversión)
Route::get('/vende-tu-propiedad', [LandingController::class, 'show'])->name('landing.vende');
Route::post('/landing/submit', [LandingController::class, 'submit'])->middleware('throttle:public-form')->name('landing.submit');

// Documentos legales públicos
Route::get('/legal/{slug}', [LegalPageController::class, 'show'])->name('legal.public');

// Auth Routes (solo para invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    // Recuperacion de contrasena
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.forgot');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:forgot-password');
    Route::get('/reset-password', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:forgot-password');
});

// Rutas autenticadas
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // CRUD protegido por autenticación
    Route::resource('properties', PropertyController::class);
    Route::post('properties/{property}/publish-easybroker', [PropertyController::class, 'publishToEasyBroker'])->name('properties.publish-easybroker');
    Route::post('properties/{property}/unpublish-easybroker', [PropertyController::class, 'unpublishFromEasyBroker'])->name('properties.unpublish-easybroker');
    Route::post('properties/{property}/photos', [PropertyPhotoController::class, 'store'])->name('properties.photos.store');
    Route::patch('properties/{property}/photos/{photo}/primary', [PropertyPhotoController::class, 'setPrimary'])->name('properties.photos.primary');
    Route::patch('properties/{property}/photos/{photo}', [PropertyPhotoController::class, 'update'])->name('properties.photos.update');
    Route::post('properties/{property}/photos/reorder', [PropertyPhotoController::class, 'reorder'])->name('properties.photos.reorder');
    Route::delete('properties/{property}/photos/{photo}', [PropertyPhotoController::class, 'destroy'])->name('properties.photos.destroy');
    Route::get('properties/{property}/pdf', [PropertyFichaController::class, 'pdf'])->name('properties.pdf');
    Route::post('properties/{property}/send-ficha', [PropertyFichaController::class, 'email'])->name('properties.send-ficha');
    // QR Codes
    Route::post('properties/{property}/qr/generate', [PropertyQrController::class, 'generate'])->name('properties.qr.generate');
    Route::get('properties/{property}/qr/download', [PropertyQrController::class, 'download'])->name('properties.qr.download');
    Route::delete('properties/{property}/qr', [PropertyQrController::class, 'delete'])->name('properties.qr.delete');
    Route::resource('clients', ClientController::class);
    Route::get('clients/{client}/email', [ClientEmailController::class, 'compose'])->name('clients.email.compose');
    Route::post('clients/{client}/email', [ClientEmailController::class, 'send'])->name('clients.email.send');
    Route::get('client-emails/{email}', [ClientEmailController::class, 'show'])->name('clients.email.show');
    Route::post('clients/{client}/interaction', [ClientController::class, 'storeInteraction'])->name('clients.interaction.store');
    Route::post('clients/{client}/create-portal', [ClientController::class, 'createPortalAccount'])->name('clients.create-portal');
    Route::patch('clients/{client}/toggle-portal', [ClientController::class, 'togglePortalAccess'])->name('clients.toggle-portal');
    Route::delete('clients/{client}/delete-portal', [ClientController::class, 'deletePortalAccess'])->name('clients.delete-portal');
    Route::post('clients/{client}/reset-portal-password', [ClientController::class, 'resetPortalPassword'])->name('clients.reset-portal-password');
    Route::post('clients/{client}/contrato-confidencialidad', [\App\Http\Controllers\ClientContratoController::class, 'enviarConfidencialidad'])->name('admin.clients.contrato-confidencialidad');
    Route::resource('brokers', BrokerController::class);
    Route::resource('broker-companies', BrokerCompanyController::class);
    Route::resource('referrers', ReferrerController::class);
    Route::post('referrers/{referrer}/referrals', [ReferrerController::class, 'storeReferral'])->name('referrers.referrals.store');
    Route::patch('referrals/{referral}/status', [ReferrerController::class, 'updateReferralStatus'])->name('referrals.update-status');
    Route::patch('referrals/{referral}/link', [ReferrerController::class, 'linkReferral'])->name('referrals.link');

    // Deals
    Route::resource('deals', DealController::class);
    Route::patch('deals/{deal}/stage', [DealController::class, 'updateStage'])->name('deals.update-stage');

    // Operaciones (pipeline unificado)
    Route::resource('operations', OperationController::class);
    Route::patch('operations/{operation}/stage', [OperationController::class, 'updateStage'])->name('operations.update-stage');
    Route::patch('operations/{operation}/checklist/{item}', [OperationController::class, 'toggleChecklist'])->name('operations.checklist.toggle');
    Route::post('operations/{operation}/documents', [RentalDocumentController::class, 'storeForOperation'])->name('operations.documents.store');
    Route::post('operations/{operation}/poliza', [PolizaJuridicaController::class, 'storeForOperation'])->name('operations.poliza.store');
    Route::post('operations/{operation}/contracts/generate', [ContractController::class, 'generateForOperation'])->name('operations.contracts.generate');
    Route::post('operations/{operation}/contracts/upload', [ContractController::class, 'uploadForOperation'])->name('operations.contracts.upload');
    Route::post('operations/{operation}/comments', [OperationController::class, 'storeComment'])->name('operations.comments.store');

    // Rentas
    Route::resource('rentals', RentalProcessController::class);
    Route::patch('rentals/{rental}/stage', [RentalProcessController::class, 'updateStage'])->name('rentals.update-stage');
    Route::post('rentals/{rental}/documents', [RentalDocumentController::class, 'store'])->name('rentals.documents.store');
    Route::patch('documents/{document}/status', [RentalDocumentController::class, 'updateStatus'])->name('documents.update-status');
    Route::get('documents/{document}/download', [RentalDocumentController::class, 'download'])->name('documents.download');
    Route::delete('documents/{document}', [RentalDocumentController::class, 'destroy'])->name('documents.destroy');

    // Poliza Juridica
    Route::post('rentals/{rental}/poliza', [PolizaJuridicaController::class, 'store'])->name('rentals.poliza.store');
    Route::put('polizas/{poliza}', [PolizaJuridicaController::class, 'update'])->name('polizas.update');
    Route::patch('polizas/{poliza}/status', [PolizaJuridicaController::class, 'updateStatus'])->name('polizas.update-status');
    Route::post('polizas/{poliza}/events', [PolizaJuridicaController::class, 'addEvent'])->name('polizas.events.store');

    // Contratos
    Route::post('rentals/{rental}/contracts/generate', [ContractController::class, 'generate'])->name('rentals.contracts.generate');
    Route::post('rentals/{rental}/contracts/upload', [ContractController::class, 'upload'])->name('rentals.contracts.upload');
    Route::get('contracts/{contract}/preview', [ContractController::class, 'preview'])->name('contracts.preview');
    Route::get('contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::post('contracts/{contract}/sign', [ContractController::class, 'sign'])->name('contracts.sign');
    Route::post('contracts/{contract}/send-signature', [ContractController::class, 'sendForSignature'])->name('contracts.send-signature');
    Route::delete('contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');

    // Tareas
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])->name('tasks.toggleComplete');

    // Perfil del usuario
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/mail-settings', [ProfileController::class, 'updateMailSettings'])->name('profile.mail-settings');
    Route::post('/profile/mail-settings/test', [ProfileController::class, 'testMailConnection'])->name('profile.mail-settings.test');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Help Center (user-facing)
    Route::get('/help', [HelpCenterController::class, 'index'])->name('help.index');
    Route::get('/help/article/{article:slug}', [HelpCenterController::class, 'show'])->name('help.article');
    Route::get('/help/tips/{context}', [HelpCenterController::class, 'tips'])->name('help.tips');
    Route::post('/help/onboarding/complete-step', [HelpCenterController::class, 'completeStep'])->name('help.onboarding.complete');
    Route::get('/api/users/search', [NotificationController::class, 'searchUsers'])->name('api.users.search');
    Route::get('/api/clients/search', [ClientController::class, 'search'])->name('api.clients.search');
});

// Admin Routes (admin, editor, viewer tienen acceso al panel)
Route::middleware(['auth', 'viewer'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // IMPORTANTE: Rutas específicas ANTES del resource para que no sean "comidas" por {user}
    Route::get('users/permissions', [UserAdminController::class, 'permissions'])->name('users.permissions');
    Route::post('users/{user}/avatar', [UserAdminController::class, 'uploadAvatar'])->name('users.avatar');
    Route::post('users/{user}/permissions', [UserAdminController::class, 'updatePermissions'])->name('users.updatePermissions');
    Route::post('users/{user}/role', [UserAdminController::class, 'changeRole'])->name('users.changeRole');

    // Resource de usuarios (genera admin.users.index, admin.users.create, admin.users.show, etc.)
    Route::resource('users', UserAdminController::class)->names('users');

    // Solo admin: configuración
    Route::middleware('admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Agentes IA
        Route::get('/ai-config', [\App\Http\Controllers\Admin\AiConfigController::class, 'index'])->name('ai-config');
        Route::patch('/ai-config/{agent}', [\App\Http\Controllers\Admin\AiConfigController::class, 'update'])->name('ai-config.update');

        // Precios de Mercado (admin — actualización manual)
        Route::get('/market/prices',     [\App\Http\Controllers\Admin\MarketPricesController::class, 'index'])->name('market.prices');
        Route::post('/market/prices/run',[\App\Http\Controllers\Admin\MarketPricesController::class, 'run'])->name('market.prices.run');

        // Homepage CMS
        Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');
        Route::post('/homepage', [HomepageController::class, 'update'])->name('homepage.update');

        // Page editors
        Route::get('/servicios-page', [ServiciosPageController::class, 'index'])->name('servicios-page');
        Route::post('/servicios-page', [ServiciosPageController::class, 'update'])->name('servicios-page.update');
        Route::get('/nosotros-page', [NosotrosPageController::class, 'index'])->name('nosotros-page');
        Route::post('/nosotros-page', [NosotrosPageController::class, 'update'])->name('nosotros-page.update');
        Route::post('/nosotros-page/toggle-team/{user}', [NosotrosPageController::class, 'toggleTeamMember'])->name('nosotros-page.toggle-team');
        Route::post('/nosotros-page/team-order', [NosotrosPageController::class, 'updateTeamOrder'])->name('nosotros-page.team-order');
        Route::get('/vender-page', [VenderPageController::class, 'index'])->name('vender-page');
        Route::post('/vender-page', [VenderPageController::class, 'update'])->name('vender-page.update');

        // Email settings
        Route::get('/email/settings', [EmailSettingsController::class, 'index'])->name('email.settings');
        Route::post('/email/settings', [EmailSettingsController::class, 'update'])->name('email.settings.update');
        Route::post('/email/settings/test', [EmailSettingsController::class, 'test'])->name('email.settings.test-connection');
        Route::post('/email/settings/send-test', [EmailSettingsController::class, 'sendTest'])->name('email.settings.send-test');

        // Email templates
        Route::post('/email/templates/upload-image', [EmailTemplateController::class, 'uploadImage'])->name('email.templates.upload-image');
        Route::post('/email/templates/send-test', [EmailTemplateController::class, 'sendTest'])->name('email.templates.send-test');
        Route::get('/email/templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('email.templates.preview');
        Route::resource('/email/templates', EmailTemplateController::class)->names('email.templates')->parameters(['templates' => 'template']);

        // Email assets (media gallery)
        Route::get('/email/assets/gallery', [EmailAssetController::class, 'gallery'])->name('email.assets.gallery');
        Route::resource('/email/assets', EmailAssetController::class)->names('email.assets')->only(['index', 'store', 'destroy'])->parameters(['assets' => 'asset']);

        // EasyBroker settings
        Route::get('/easybroker/settings', [EasyBrokerSettingsController::class, 'index'])->name('easybroker.settings');
        Route::post('/easybroker/settings', [EasyBrokerSettingsController::class, 'update'])->name('easybroker.settings.update');
        Route::post('/easybroker/settings/test', [EasyBrokerSettingsController::class, 'test'])->name('easybroker.settings.test');

        // Integraciones (tracking codes, APIs)
        Route::get('/integrations', [IntegrationSettingsController::class, 'index'])->name('integrations.index');
        Route::post('/integrations', [IntegrationSettingsController::class, 'update'])->name('integrations.update');
        Route::post('/integrations/webhook/regenerate', [IntegrationSettingsController::class, 'regenerateWebhookKey'])->name('integrations.webhook.regenerate');

        // Automatizaciones
        Route::get('/automations', [AutomationController::class, 'index'])->name('automations.index');

        // Plantillas de Contrato
        Route::get('contract-templates/{contract_template}/preview', [ContractTemplateController::class, 'preview'])->name('contract-templates.preview');
        Route::resource('contract-templates', ContractTemplateController::class)->names('contract-templates');

        // Checklists de Operaciones
        Route::resource('checklists', ChecklistTemplateController::class)->names('checklists');
        Route::get('/automations/logs', [AutomationController::class, 'logs'])->name('automations.logs');
        Route::get('/automations/create', [AutomationController::class, 'create'])->name('automations.create');
        Route::post('/automations', [AutomationController::class, 'store'])->name('automations.store');
        Route::get('/automations/{automation}/edit', [AutomationController::class, 'edit'])->name('automations.edit');
        Route::put('/automations/{automation}', [AutomationController::class, 'update'])->name('automations.update');
        Route::patch('/automations/{automation}/toggle', [AutomationController::class, 'toggleActive'])->name('automations.toggle');
        Route::delete('/automations/{automation}', [AutomationController::class, 'destroy'])->name('automations.destroy');

        // Finanzas
        Route::get('/finance', [FinanceController::class, 'dashboard'])->name('finance.dashboard');
        Route::get('/finance/transactions', [FinanceController::class, 'transactions'])->name('finance.transactions');
        Route::get('/finance/transactions/create', [FinanceController::class, 'createTransaction'])->name('finance.transactions.create');
        Route::post('/finance/transactions', [FinanceController::class, 'storeTransaction'])->name('finance.transactions.store');
        Route::get('/finance/transactions/{transaction}/edit', [FinanceController::class, 'editTransaction'])->name('finance.transactions.edit');
        Route::put('/finance/transactions/{transaction}', [FinanceController::class, 'updateTransaction'])->name('finance.transactions.update');
        Route::delete('/finance/transactions/{transaction}', [FinanceController::class, 'destroyTransaction'])->name('finance.transactions.destroy');
        Route::get('/finance/commissions', [FinanceController::class, 'commissions'])->name('finance.commissions');
        Route::post('/finance/commissions/{commission}/approve', [FinanceController::class, 'approveCommission'])->name('finance.commissions.approve');
        Route::post('/finance/commissions/{commission}/pay', [FinanceController::class, 'payCommission'])->name('finance.commissions.pay');

        // CMS: Posts y Paginas
        Route::post('cms/upload-image', [PostController::class, 'uploadImage'])->name('cms.upload-image');
        Route::resource('posts', PostController::class)->names('posts');
        Route::get('content-calendar', [ContentCalendarController::class, 'index'])->name('content-calendar');
        Route::get('content-calendar/events', [ContentCalendarController::class, 'events'])->name('content-calendar.events');
        Route::patch('content-calendar/{post}/date', [ContentCalendarController::class, 'updateDate'])->name('content-calendar.update-date');
        Route::resource('pages', PageController::class)->names('pages');
        Route::resource('post-categories', PostCategoryController::class)->names('post-categories')->only(['index', 'store', 'update', 'destroy']);
        Route::resource('tags', TagController::class)->names('tags')->only(['index', 'store', 'update', 'destroy']);

        // Leads / Contactos
        Route::resource('submissions', ContactSubmissionController::class)->names('submissions')->only(['index', 'show', 'destroy']);

        // Media Library
        Route::get('media/browse', [MediaController::class, 'browse'])->name('media.browse');
        Route::resource('media', MediaController::class)->names('media')->only(['index', 'store', 'update', 'destroy']);

        // Menus
        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
        Route::post('menus/{menu}/items', [MenuController::class, 'updateItems'])->name('menus.update-items');

        // Footer
        Route::get('footer', [FooterController::class, 'index'])->name('footer');
        Route::post('footer', [FooterController::class, 'update'])->name('footer.update');

        // Form Builder
        Route::get('forms/{form}/submissions', [FormController::class, 'submissions'])->name('forms.submissions');
        Route::resource('forms', FormController::class)->names('forms');

        // Marketing
        Route::get('/marketing', [MarketingController::class, 'dashboard'])->name('marketing.dashboard');
        Route::get('/marketing/channels', [MarketingController::class, 'channels'])->name('marketing.channels');
        Route::post('/marketing/channels', [MarketingController::class, 'storeChannel'])->name('marketing.channels.store');
        Route::put('/marketing/channels/{channel}', [MarketingController::class, 'updateChannel'])->name('marketing.channels.update');
        Route::delete('/marketing/channels/{channel}', [MarketingController::class, 'destroyChannel'])->name('marketing.channels.destroy');
        Route::get('/marketing/campaigns', [MarketingController::class, 'campaigns'])->name('marketing.campaigns');
        Route::get('/marketing/campaigns/create', [MarketingController::class, 'createCampaign'])->name('marketing.campaigns.create');
        Route::post('/marketing/campaigns', [MarketingController::class, 'storeCampaign'])->name('marketing.campaigns.store');
        Route::get('/marketing/campaigns/{campaign}/edit', [MarketingController::class, 'editCampaign'])->name('marketing.campaigns.edit');
        Route::put('/marketing/campaigns/{campaign}', [MarketingController::class, 'updateCampaign'])->name('marketing.campaigns.update');
        Route::delete('/marketing/campaigns/{campaign}', [MarketingController::class, 'destroyCampaign'])->name('marketing.campaigns.destroy');

        // Segments
        Route::get('/marketing/segments', [SegmentController::class, 'index'])->name('segments.index');
        Route::get('/marketing/segments/create', [SegmentController::class, 'create'])->name('segments.create');
        Route::post('/marketing/segments', [SegmentController::class, 'store'])->name('segments.store');
        Route::get('/marketing/segments/{segment}/edit', [SegmentController::class, 'edit'])->name('segments.edit');
        Route::put('/marketing/segments/{segment}', [SegmentController::class, 'update'])->name('segments.update');
        Route::delete('/marketing/segments/{segment}', [SegmentController::class, 'destroy'])->name('segments.destroy');
        Route::post('/marketing/segments/{segment}/evaluate', [SegmentController::class, 'evaluate'])->name('segments.evaluate');
        Route::post('/marketing/segments/preview', [SegmentController::class, 'preview'])->name('segments.preview');

        // Automations Engine
        Route::get('/marketing/automations', [AutomationEngineController::class, 'index'])->name('automations-engine.index');
        Route::get('/marketing/automations/create', [AutomationEngineController::class, 'create'])->name('automations-engine.create');
        Route::post('/marketing/automations', [AutomationEngineController::class, 'store'])->name('automations-engine.store');
        Route::get('/marketing/automations/{automation}', [AutomationEngineController::class, 'show'])->name('automations-engine.show');
        Route::get('/marketing/automations/{automation}/edit', [AutomationEngineController::class, 'edit'])->name('automations-engine.edit');
        Route::put('/marketing/automations/{automation}', [AutomationEngineController::class, 'update'])->name('automations-engine.update');
        Route::delete('/marketing/automations/{automation}', [AutomationEngineController::class, 'destroy'])->name('automations-engine.destroy');
        Route::post('/marketing/automations/{automation}/toggle', [AutomationEngineController::class, 'toggle'])->name('automations-engine.toggle');
        Route::post('/marketing/automations/{automation}/enroll', [AutomationEngineController::class, 'enrollClients'])->name('automations-engine.enroll');

        // Lead Scoring
        Route::get('/marketing/scoring', [LeadScoringController::class, 'index'])->name('scoring.index');
        Route::put('/marketing/scoring/rules', [LeadScoringController::class, 'updateRules'])->name('scoring.rules.update');
        Route::get('/marketing/scoring/client/{client}', [LeadScoringController::class, 'clientTimeline'])->name('scoring.client.timeline');

        // Messages
        Route::get('/marketing/messages', [MessageController::class, 'index'])->name('messages.index');

        // Newsletter
        Route::get('/newsletters/subscribers', [NewsletterController::class, 'index'])->name('newsletters.subscribers');
        Route::post('/newsletters/subscribers', [NewsletterController::class, 'store'])->name('newsletters.subscribers.store');
        Route::get('/newsletters/subscribers/export', [NewsletterController::class, 'export'])->name('newsletters.subscribers.export');
        Route::delete('/newsletters/subscribers/{subscriber}', [NewsletterController::class, 'destroy'])->name('newsletters.subscribers.destroy');
        Route::get('/newsletters/campaigns', [NewsletterController::class, 'campaigns'])->name('newsletters.campaigns');
        Route::get('/newsletters/campaigns/create', [NewsletterController::class, 'createCampaign'])->name('newsletters.campaigns.create');
        Route::post('/newsletters/campaigns', [NewsletterController::class, 'storeCampaign'])->name('newsletters.campaigns.store');
        Route::get('/newsletters/campaigns/{campaign}', [NewsletterController::class, 'showCampaign'])->name('newsletters.campaigns.show');
        Route::get('/newsletters/campaigns/{campaign}/edit', [NewsletterController::class, 'editCampaign'])->name('newsletters.campaigns.edit');
        Route::put('/newsletters/campaigns/{campaign}', [NewsletterController::class, 'updateCampaign'])->name('newsletters.campaigns.update');
        Route::delete('/newsletters/campaigns/{campaign}', [NewsletterController::class, 'destroyCampaign'])->name('newsletters.campaigns.destroy');
        Route::get('/newsletters/campaigns/{campaign}/preview', [NewsletterController::class, 'previewCampaign'])->name('newsletters.campaigns.preview');
        Route::post('/newsletters/campaigns/{campaign}/send', [NewsletterController::class, 'sendCampaign'])->name('newsletters.campaigns.send');

        // Testimonials
        Route::resource('testimonials', TestimonialController::class)->except(['show']);

        // Help Center (admin management)
        Route::get('/help/manage', [HelpCenterController::class, 'adminIndex'])->name('help.manage');
        Route::post('/help/articles', [HelpCenterController::class, 'storeArticle'])->name('help.articles.store');
        Route::put('/help/articles/{article}', [HelpCenterController::class, 'updateArticle'])->name('help.articles.update');
        Route::delete('/help/articles/{article}', [HelpCenterController::class, 'destroyArticle'])->name('help.articles.destroy');
        Route::post('/help/tips', [HelpCenterController::class, 'storeTip'])->name('help.tips.store');
        Route::delete('/help/tips/{tip}', [HelpCenterController::class, 'destroyTip'])->name('help.tips.destroy');

        // Legal / Documentos legales
        Route::prefix('legal')->name('legal.')->group(function () {
            Route::get('/', [LegalController::class, 'index'])->name('index');
            Route::get('/create', [LegalController::class, 'create'])->name('create');
            Route::post('/', [LegalController::class, 'store'])->name('store');
            Route::get('/acceptances', [LegalController::class, 'allAcceptances'])->name('acceptances');
            Route::get('/{document}', [LegalController::class, 'show'])->name('show');
            Route::get('/{document}/edit', [LegalController::class, 'edit'])->name('edit');
            Route::put('/{document}', [LegalController::class, 'update'])->name('update');
            Route::delete('/{document}', [LegalController::class, 'destroy'])->name('destroy');
            Route::get('/{document}/acceptances', [LegalController::class, 'acceptances'])->name('document.acceptances');
        });
    });

    // Gestión de brokers
    Route::get('/brokers-mgmt', [BrokerManagementController::class, 'index'])->name('brokers');
    Route::post('/brokers-mgmt/{user}/approve', [BrokerManagementController::class, 'approveBroker'])->name('brokers.approve');
    Route::post('/brokers-mgmt/{user}/revoke', [BrokerManagementController::class, 'revokeBroker'])->name('brokers.revoke');
    Route::post('/brokers-mgmt/{user}/make-admin', [BrokerManagementController::class, 'makeAdmin'])->name('brokers.makeAdmin');

    // ===== CARRUSELES IG =====
    Route::prefix('carousels')->name('carousels.')->group(function () {
        // Templates (admin only)
        Route::middleware('admin')->prefix('templates')->name('templates.')->group(function () {
            Route::get('/',              [\App\Http\Controllers\Admin\CarouselTemplateController::class, 'index'])->name('index');
            Route::get('/create',        [\App\Http\Controllers\Admin\CarouselTemplateController::class, 'create'])->name('create');
            Route::post('/',             [\App\Http\Controllers\Admin\CarouselTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit',   [\App\Http\Controllers\Admin\CarouselTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}',        [\App\Http\Controllers\Admin\CarouselTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}',     [\App\Http\Controllers\Admin\CarouselTemplateController::class, 'destroy'])->name('destroy');
        });

        // Image generation test/diagnostic — must be BEFORE /{carousel} wildcard
        Route::get('/image-test',  [\App\Http\Controllers\Admin\CarouselImageTestController::class, 'show'])->name('image-test');
        Route::post('/image-test', [\App\Http\Controllers\Admin\CarouselImageTestController::class, 'test'])->name('image-test.run');

        // Prompt settings — must be BEFORE /{carousel} wildcard
        Route::get('/prompts',         [\App\Http\Controllers\Admin\CarouselPromptController::class, 'index'])->name('prompts');
        Route::post('/prompts',        [\App\Http\Controllers\Admin\CarouselPromptController::class, 'update'])->name('prompts.update');
        Route::post('/prompts/reset',  [\App\Http\Controllers\Admin\CarouselPromptController::class, 'reset'])->name('prompts.reset');
        Route::post('/prompts/preview',[\App\Http\Controllers\Admin\CarouselPromptController::class, 'preview'])->name('prompts.preview');

        // Topic discovery — must be BEFORE /{carousel} wildcard
        Route::get('/discovery',                     [\App\Http\Controllers\Admin\CarouselDiscoveryController::class, 'form'])->name('discovery.form');
        Route::post('/discovery',                    [\App\Http\Controllers\Admin\CarouselDiscoveryController::class, 'discover'])->name('discovery.discover');
        Route::get('/discovery/{session}',           [\App\Http\Controllers\Admin\CarouselDiscoveryController::class, 'review'])->name('discovery.review');
        Route::post('/discovery/{session}/generate', [\App\Http\Controllers\Admin\CarouselDiscoveryController::class, 'generate'])->name('discovery.generate');

        // Main carousel CRUD
        Route::get('/',                  [\App\Http\Controllers\Admin\CarouselController::class, 'index'])->name('index');
        Route::get('/create',            [\App\Http\Controllers\Admin\CarouselController::class, 'create'])->name('create');
        Route::post('/',                 [\App\Http\Controllers\Admin\CarouselController::class, 'store'])->name('store');
        Route::get('/{carousel}',        [\App\Http\Controllers\Admin\CarouselController::class, 'show'])->name('show');
        Route::get('/{carousel}/edit',   [\App\Http\Controllers\Admin\CarouselController::class, 'edit'])->name('edit');
        Route::put('/{carousel}',        [\App\Http\Controllers\Admin\CarouselController::class, 'update'])->name('update');
        Route::delete('/{carousel}',     [\App\Http\Controllers\Admin\CarouselController::class, 'destroy'])->name('destroy');

        // AI generation
        Route::get('/{carousel}/generate',            [\App\Http\Controllers\Admin\CarouselAIController::class, 'showForm'])->name('generate');
        Route::post('/{carousel}/generate',           [\App\Http\Controllers\Admin\CarouselAIController::class, 'generate'])->name('generate.run');
        Route::post('/{carousel}/regenerate-caption', [\App\Http\Controllers\Admin\CarouselAIController::class, 'regenerateCaption'])->name('regenerate-caption');

        // Render pipeline
        Route::post('/{carousel}/render',                [\App\Http\Controllers\Admin\CarouselRenderController::class, 'renderAll'])->name('render');
        Route::get('/{carousel}/render/status',          [\App\Http\Controllers\Admin\CarouselRenderController::class, 'status'])->name('render.status');
        Route::get('/{carousel}/download',               [\App\Http\Controllers\Admin\CarouselRenderController::class, 'downloadSlides'])->name('download');
        Route::post('/{carousel}/slides/{slide}/render',   [\App\Http\Controllers\Admin\CarouselRenderController::class, 'renderSlide'])->name('slides.render');
        Route::delete('/{carousel}/slides/{slide}/render', [\App\Http\Controllers\Admin\CarouselRenderController::class, 'clearRender'])->name('slides.render.clear');

        // Approval & publishing
        Route::post('/{carousel}/approve', [\App\Http\Controllers\Admin\CarouselApprovalController::class, 'approve'])->name('approve');
        Route::post('/{carousel}/reject',  [\App\Http\Controllers\Admin\CarouselApprovalController::class, 'reject'])->name('reject');
        Route::post('/{carousel}/webhook', [\App\Http\Controllers\Admin\CarouselApprovalController::class, 'webhook'])->name('webhook');

        // Slide content editing (autosave)
        Route::patch('/{carousel}/slides/{slide}', [\App\Http\Controllers\Admin\CarouselSlideController::class, 'update'])->name('slides.update');

        // Slide images (DALL-E + upload)
        Route::post('/{carousel}/generate-images',                      [\App\Http\Controllers\Admin\CarouselSlideController::class, 'generateImages'])->name('images.generate');
        Route::post('/{carousel}/slides/{slide}/generate-image',        [\App\Http\Controllers\Admin\CarouselSlideController::class, 'generateImage'])->name('slides.image.generate');
        Route::post('/{carousel}/slides/{slide}/background',            [\App\Http\Controllers\Admin\CarouselSlideController::class, 'uploadBackground'])->name('slides.background.upload');
        Route::delete('/{carousel}/slides/{slide}/background',          [\App\Http\Controllers\Admin\CarouselSlideController::class, 'removeBackground'])->name('slides.background.remove');
    });

    // ===== OPINIÓN DE VALOR =====
    Route::prefix('valuations')->name('valuations.')->group(function () {
        Route::get('/',                              [\App\Http\Controllers\Admin\ValuationController::class, 'index'])->name('index');
        Route::get('/analytics',                     [\App\Http\Controllers\Admin\ValuationController::class, 'analytics'])->name('analytics');
        Route::get('/create',                        [\App\Http\Controllers\Admin\ValuationController::class, 'create'])->name('create');
        Route::post('/',                             [\App\Http\Controllers\Admin\ValuationController::class, 'store'])->name('store');
        Route::get('/{valuation}',                   [\App\Http\Controllers\Admin\ValuationController::class, 'show'])->name('show');
        Route::get('/{valuation}/edit',              [\App\Http\Controllers\Admin\ValuationController::class, 'edit'])->name('edit');
        Route::put('/{valuation}',                   [\App\Http\Controllers\Admin\ValuationController::class, 'update'])->name('update');
        Route::patch('/{valuation}/status',          [\App\Http\Controllers\Admin\ValuationController::class, 'updateStatus'])->name('status');
        Route::get('/{valuation}/pdf',           [\App\Http\Controllers\Admin\ValuationController::class, 'pdf'])->name('pdf');
        Route::post('/{valuation}/record-sale',  [\App\Http\Controllers\Admin\ValuationController::class, 'recordSale'])->name('record-sale');
        Route::delete('/{valuation}',                [\App\Http\Controllers\Admin\ValuationController::class, 'destroy'])->name('destroy');
    });
});

// ===== PORTAL DE CLIENTE =====
Route::middleware(['auth', 'client'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/rentals', [PortalRentalController::class, 'index'])->name('rentals.index');
    Route::get('/rentals/{id}', [PortalRentalController::class, 'show'])->name('rentals.show');
    Route::get('/documents', [PortalDocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{id}/download', [PortalDocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/upload', [PortalDocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/account', [PortalDashboardController::class, 'account'])->name('account');
    Route::put('/account/password', [PortalDashboardController::class, 'updatePassword'])->name('account.password');
});

// ── Firma pública — estado del proceso de firma ──────────────────────────────
Route::get('/firma/{token}', [\App\Http\Controllers\ContratoPublicoController::class, 'show'])
    ->name('firma.show');

// ── Test Google Docs Template (temporal — quitar en producción) ──────────────
Route::middleware(['auth', 'admin'])->get('/test-google-docs', function () {
    $templateId = config('services.google_drive.template_confidencialidad');

    if (!$templateId) {
        return response()->json([
            'ok'   => false,
            'hint' => 'Agrega GOOGLE_DOCS_TEMPLATE_CONFIDENCIALIDAD=<id_del_template> en .env',
        ]);
    }

    // ── Diagnóstico: listar archivos visibles para el SA en el Shared Drive ──
    try {
        $driveService = app(\App\Services\GoogleDriveService::class);
        $driveClient  = $driveService->getDriveClient();
        $driveApi     = new \Google\Service\Drive($driveClient);

        $sharedDriveId = '0AJnAtWjE68kvUk9PVA';

        $list = $driveApi->files->listFiles([
            'corpora'                   => 'drive',
            'driveId'                   => $sharedDriveId,
            'includeItemsFromAllDrives' => true,
            'supportsAllDrives'         => true,
            'fields'                    => 'files(id,name,mimeType)',
            'pageSize'                  => 20,
        ]);

        $visibleFiles = array_map(
            fn($f) => ['id' => $f->getId(), 'name' => $f->getName(), 'mime' => $f->getMimeType()],
            $list->getFiles()
        );

        // Intentar get directo del template
        try {
            $file     = $driveApi->files->get($templateId, ['supportsAllDrives' => true, 'fields' => 'id,name']);
            $fileInfo = ['id' => $file->getId(), 'name' => $file->getName()];
        } catch (\Throwable $e) {
            $fileInfo = ['error' => $e->getMessage()];
        }

        return response()->json([
            'ok'             => false,
            'step'           => 'list_drive',
            'template_id'    => $templateId,
            'template_get'   => $fileInfo,
            'visible_files'  => $visibleFiles,
            'count'          => count($visibleFiles),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok'    => false,
            'step'  => 'list_drive_error',
            'error' => $e->getMessage(),
        ]);
    }

    try {
        /** @var \App\Services\GoogleDocsService $docs */
        $docs = app(\App\Services\GoogleDocsService::class);

        $fileId = $docs->createFromTemplate(
            templateId:    $templateId,
            documentName:  'Contrato Prueba — ' . now()->format('d/m/Y H:i'),
            replacements:  [
                '{{NOMBRE_CLIENTE}}' => 'Juan Pérez García',
                '{{EMAIL_CLIENTE}}'  => 'juan@ejemplo.com',
                '{{TELEFONO}}'       => '+52 55 1234 5678',
                '{{FECHA}}'          => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
                '{{EMPRESA}}'        => config('app.name', 'Home del Valle'),
            ],
        );

        return response()->json([
            'ok'        => true,
            'template'  => $fileInfo,
            'file_id'   => $fileId,
            'view_url'  => $docs->viewUrl($fileId),
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        return response()->json([
            'ok'    => false,
            'error' => $e->getMessage(),
        ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
});

