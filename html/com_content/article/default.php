<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;

// Create shortcuts to some parameters.
$params  = $this->item->params;
$canEdit = $params->get('access-edit');
$user    = Factory::getUser();
$info    = $params->get('info_block_position', 0);
$htag    = $this->params->get('show_page_heading') ? 'h2' : 'h1';

// Check if associations are implemented. If they are, define the parameter.
$assocParam        = (Associations::isEnabled() && $params->get('show_associations'));
$currentDate       = Factory::getDate()->format('Y-m-d H:i:s');
$isNotPublishedYet = $this->item->publish_up > $currentDate;
$isExpired         = !is_null($this->item->publish_down) && $this->item->publish_down < $currentDate;
?>
<div class="com-content-article item-page<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="https://schema.org/Article">
	<meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? Factory::getApplication()->get('language') : $this->item->language; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</div>
	<?php endif;
	if (!empty($this->item->pagination) && !$this->item->paginationposition && $this->item->paginationrelative)
	{
		echo $this->item->pagination;
	}
	?>

	<?php $useDefList = $params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
	|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam; ?>

	<div class="row mb-3 mb-lg-5">
		<?php
		if (LayoutHelper::render('joomla.content.full_image', $this->item)) {
			$left = 'col-lg-6 d-flex align-items-center';
			$right = 'col-lg-6';
		}
		else {
			$left = 'col-12 d-flex align-items-center text-center justify-content-center';
			$right = '';
		}
		?>
		<div class="<?php echo $left; ?>">
			<?php if ($params->get('show_title')) : ?>
			<div class="page-header">
				<<?php echo $htag; ?> itemprop="headline">
					<?php echo $this->escape($this->item->title); ?>
				</<?php echo $htag; ?>>
				<?php if ($this->item->state == ContentComponent::CONDITION_UNPUBLISHED) : ?>
					<span class="badge bg-warning text-light"><?php echo Text::_('JUNPUBLISHED'); ?></span>
				<?php endif; ?>
				<?php if ($isNotPublishedYet) : ?>
					<span class="badge bg-warning text-light"><?php echo Text::_('JNOTPUBLISHEDYET'); ?></span>
				<?php endif; ?>
				<?php if ($isExpired) : ?>
					<span class="badge bg-warning text-light"><?php echo Text::_('JEXPIRED'); ?></span>
				<?php endif; ?>
				<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
					<?php echo LayoutHelper::render('joomla.content.info_block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
				<?php endif; ?>
				<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
					<?php $this->item->tagLayout = new FileLayout('joomla.content.tags'); ?>
					<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php if (LayoutHelper::render('joomla.content.full_image', $this->item)) : ?>
		<div class="<?php echo $right; ?>">
			<?php if ($params->get('access-view')) : ?>
			<?php echo LayoutHelper::render('joomla.content.full_image', $this->item); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	
	<?php if ($canEdit) : ?>
		<?php echo LayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item)); ?>
	<?php endif; ?>

	<?php // Content is generated by content plugin event "onContentAfterTitle" ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php // Content is generated by content plugin event "onContentBeforeDisplay" ?>
	<?php echo $this->item->event->beforeDisplayContent; ?>

	<?php if ((int) $params->get('urls_position', 0) === 0) : ?>
	<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>
	<?php if ($params->get('access-view')) : ?>
	<?php
	if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && !$this->item->paginationrelative) :
		echo $this->item->pagination;
	endif;
	?>
	<?php if (isset ($this->item->toc)) :
		echo $this->item->toc;
	endif; ?>
	<div itemprop="articleBody" class="com-content-article__body mx-auto">
		<?php echo $this->item->text; ?>
	</div>

	<?php if ($info == 1 || $info == 2) : ?>
		<?php if ($useDefList) : ?>
			<?php echo LayoutHelper::render('joomla.content.info_block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
		<?php endif; ?>
		<?php if ($params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
			<?php $this->item->tagLayout = new FileLayout('joomla.content.tags'); ?>
			<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	if (!empty($this->item->pagination) && $this->item->paginationposition && !$this->item->paginationrelative) :
		echo $this->item->pagination;
	?>
	<?php endif; ?>
	<?php if ((int) $params->get('urls_position', 0) === 1) : ?>
	<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>
	<?php // Optional teaser intro text for guests ?>
	<?php elseif ($params->get('show_noauth') == true && $user->get('guest')) : ?>
	<?php echo LayoutHelper::render('joomla.content.intro_image', $this->item); ?>
	<?php echo HTMLHelper::_('content.prepare', $this->item->introtext); ?>
	<?php // Optional link to let them register to see the whole article. ?>
	<?php if ($params->get('show_readmore') && $this->item->fulltext != null) : ?>
	<?php $menu = Factory::getApplication()->getMenu(); ?>
	<?php $active = $menu->getActive(); ?>
	<?php $itemId = $active->id; ?>
	<?php $link = new Uri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false)); ?>
	<?php $link->setVar('return', base64_encode(RouteHelper::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language))); ?>
	<?php echo LayoutHelper::render('joomla.content.readmore', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>
	<?php endif; ?>
	<?php endif; ?>
	<?php
	if (!empty($this->item->pagination) && $this->item->paginationposition && $this->item->paginationrelative) :
		echo $this->item->pagination;
	?>
	<?php endif; ?>
	<?php // Content is generated by content plugin event "onContentAfterDisplay" ?>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
