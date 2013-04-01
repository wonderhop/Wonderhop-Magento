<?php

$colls_cat_id = (int)(string)Mage::getConfig()->getNode('localconf/modules/catalog/category/collections_cat_id');
$colls_cat = Mage::getModel('catalog/category')->load($colls_cat_id);

$sections = explode(',', (string)$colls_cat->getChildren());


// we have two sections in the menu
//  - by type
//  - by brand

$menu = array(
    'type' => array(
        'title' => 'Shop by category',
        'children' => array(),
    ),
    'brand' => array(
        'title' => 'Shop by collection',
        'children' => array(),
    ),
);


foreach($sections as $section_id)
{
    $section = Mage::getModel('catalog/category')->load((int)$section_id);
    if ($section->getName() == 'Category' )
    {
        $sec_menu = &$menu['type'];
    }
    elseif ($section->getName() == 'Brand')
    {
        $sec_menu = &$menu['brand'];
    }
    
    $children = (string)$section->getChildren();
    if ( ! $children) continue;
    
    $types = explode( ',' , $children);
    foreach($types as $type_id)
    {
        $type = Mage::getModel('catalog/category')->load((int)$type_id);
        
        $item = array(
            'title' => $type->getName(),
            'url' => Mage::getBaseUrl() . $type->getUrlPath(),
            'children' => array(),
        );
        
        $children = (string)$type->getChildren();
        if ($children)
        {
            $subs = explode( ',' , $children);
            foreach($subs as $sub_id)
            {
                $sub = Mage::getModel('catalog/category')->load((int)$sub_id);
                $sub_item = array(
                    'title' => $sub->getName(),
                    'url' => Mage::getBaseUrl() . $sub->getUrlPath(),
                    'children' => array(),
                );
            
                $item['children'][] = $sub_item;
            }
        }
        
        $sec_menu['children'][] = $item;
    }

}

//echo '<pre>';
//var_dump($menu);
//echo '</pre>';
?>
<ul id ="sidebar_nav" class="sidebar-nav level0">
<?php foreach($menu as $top) { ?>
        
    <li class="sidebar-nav-heading"><h4><?php echo $top['title']; ?></h4></li>
    <?php if ($top['children']) { ?>

    <li class="sidebar-nav-children">
        <ul class="sidebar-nav level1">
        <?php foreach($top['children'] as $child) { ?>
            
            <li class="sidebar-nav-heading"><a href="<?php echo $child['url']; ?>"><?php echo $child['title']; ?></a></li>
            <?php if ($child['children']) { ?>
                
                <li class="sidebar-nav-children">
                    <ul class="sidebar-nav level2">
                    <?php foreach($child['children'] as $sub) { ?>
                        <li class="sidebar-nav-heding"><a href="<?php echo $sub['url']; ?>"><?php echo $sub['title']; ?></a></li>
                    <?php } ?>
                    </ul>
                </li>
            
            <?php } ?>
            
        <?php } ?>
        </ul>
    </li>
    
    <?php } ?>
    
<?php } ?>
</ul>
