<?php defined('_JEXEC') or die;use Joomla\CMS\HTML\HTMLHelper;use Joomla\CMS\Router\Route; ?>
<div class="container-fluid"><h1>Touring companies, agencies &amp; unions</h1>
<div class="card mb-4"><div class="card-body"><h2 class="h4">Add organisation</h2>
<form method="post" enctype="multipart/form-data" action="<?php echo Route::_('index.php?option=com_kaarbooking&task=companies.save'); ?>" class="row g-3">
<div class="col-md-4"><input class="form-control" name="name" required maxlength="190" placeholder="Organisation name"></div>
<div class="col-md-3"><select class="form-select" name="organisation_type"><option value="touring_company">Touring company</option><option value="travel_agency">Travel agency</option><option value="transport_union">Transport union/cooperative</option><option value="individual_operator">Individual operator</option></select></div>
<div class="col-md-3"><input class="form-control" name="legal_type" maxlength="64" placeholder="Legal type (Pvt Ltd, LLP…)"></div>
<div class="col-md-3"><input class="form-control" name="registration_number" maxlength="100" placeholder="Registration number"></div>
<div class="col-md-3"><input class="form-control" name="owner_name" maxlength="190" placeholder="Owner/contact name"></div>
<div class="col-md-3"><input class="form-control" type="email" name="email" maxlength="190" placeholder="Email"></div>
<div class="col-md-3"><input class="form-control" name="phone" maxlength="40" placeholder="Phone"></div>
<div class="col-md-4"><input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp" aria-label="Organisation logo"></div>
<div class="col-md-6"><textarea class="form-control" name="address" required placeholder="Registered address"></textarea></div>
<div class="col-md-3"><button class="btn btn-primary">Add organisation</button></div><?php echo HTMLHelper::_('form.token'); ?></form></div></div>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Logo</th><th>Name</th><th>Role</th><th>Legal type</th><th>Registration</th><th>Owner</th><th>Status</th></tr></thead><tbody>
<?php foreach($this->items as $item): ?><tr><td><?php if(!empty($item['logo_path'])): ?><img src="<?php echo htmlspecialchars(\Joomla\CMS\Uri\Uri::root().$item['logo_path'],ENT_QUOTES,'UTF-8'); ?>" alt="" width="48" height="48" loading="lazy"><?php endif; ?></td><td><?php echo htmlspecialchars($item['name'],ENT_QUOTES,'UTF-8'); ?></td><td><?php echo htmlspecialchars($item['organisation_type'],ENT_QUOTES,'UTF-8'); ?></td><td><?php echo htmlspecialchars($item['legal_type'],ENT_QUOTES,'UTF-8'); ?></td><td><?php echo htmlspecialchars($item['registration_number'],ENT_QUOTES,'UTF-8'); ?></td><td><?php echo htmlspecialchars($item['owner_name'],ENT_QUOTES,'UTF-8'); ?></td><td><?php echo htmlspecialchars($item['status'],ENT_QUOTES,'UTF-8'); ?></td></tr><?php endforeach; ?>
<?php if(!$this->items): ?><tr><td colspan="7">No organisations added.</td></tr><?php endif; ?></tbody></table></div></div>
