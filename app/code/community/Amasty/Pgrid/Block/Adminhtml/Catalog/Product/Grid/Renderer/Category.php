<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Category extends Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $categoriesHtml = '';
        $categories     = $row->getCategoryCollection()->addNameToResult();
        if ($categories)
        {
            foreach ($categories as $category)
            {
                $path        = '';
                $pathInStore = $category->getPathInStore();
                $pathIds     = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path .= $categories[$categoryId]->getName() . '/';
                    }
                }
                
                if ($path)
                {
                    $path = substr($path, 0, -1);
                    $path = '<div style="font-size: 90%; margin-bottom: 8px; border-bottom: 1px dotted #bcbcbc;">' . $path . '</div>';
                }
                
                $categoriesHtml .= $path;
            }
        }
        return $categoriesHtml;
    }
}