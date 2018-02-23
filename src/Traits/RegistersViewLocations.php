<?php

namespace Caffeinated\Themes\Traits;

trait RegistersViewLocations
{
    /**
     * Resolve and return the primary and parent themes.
     *
     * @param  string  $theme
     * @return array
     */
    protected function resolveTheme($theme)
    {
        $theme  = $this->where('slug', $theme)->first();
        $parent = null;
        $grandparent = null;
        
        if ($theme->has('parent')) {
            $parent = $this->where('slug', $theme->get('parent'))->first();
            $parent = $this->where('slug', $theme->get('parent'))->first();
            //If there is a grand parent get it
            if ($parent->has('parent')) {
                $grandparent = $this->where('slug', $parent->get('parent'))->first();
            }
        }
        
        return [$theme, $parent,$grandparent];
    }
    
    /**
     * Remove the primary and parent theme from the view finder.
     *
     * @param  Manifest  $theme
     */
    protected function removeRegisteredLocation($theme)
    {
        $current         = $this->where('slug', $this->getCurrent())->first();
        $currentLocation = config('themes.paths.absolute').'/'.$current->get('slug').'/views';
        app('view.finder')->removeLocation($currentLocation);
        
        if ($current->has('parent')) {
            $parent         = $this->where('slug', $current->get('parent'))->first();
            $parentLocation = config('themes.paths.absolute').'/'.$parent->get('slug').'/views';
            app('view.finder')->removeLocation($parentLocation);

            if ($parent->has('parent')) {
                $grandparent         = $this->where('slug', $parent->get('parent'))->first();
                $grandparentLocation = config('themes.paths.absolute').'/'.$grandparent->get('slug').'/views';
                app('view.finder')->removeLocation($grandparentLocation);
            }
        }



    }
    
//    /**
//     * Register the primary and parent theme with the view finder.
//     *
//     * @param  Manifest  $theme
//     * @param  Manifest  $parent
//     */
//    protected function addRegisteredLocation($theme, $parent)
//    {
//        if (! is_null($parent)) {
//            $parentLocation = config('themes.paths.absolute').'/'.$parent->get('slug').'/views';
//            app('view.finder')->prependLocation($parentLocation);
//        }
//
//        $themeLocation = config('themes.paths.absolute').'/'.$theme->get('slug').'/views';
//        app('view.finder')->prependLocation($themeLocation);
//    }



    /**
     * Register the primary, parent and grandparent theme with the view finder.
     *
     * @param  Manifest  $theme
     * @param  Manifest  $parent
     * @param  Manifest  $grandparent
     */
    protected function addRegisteredLocation($theme, $parent, $grandparent=null)
    {
        //register grandparent view path if exists
        if (! is_null($grandparent)) {
            $grandparentLocation = config('themes.paths.absolute').'/'.$grandparent->get('slug').'/views';
         //   var_dump($grandparentLocation);
            app('view.finder')->prependLocation($grandparentLocation);
        }

        //register parent view path if exists
        if (! is_null($parent)) {
            $parentLocation = config('themes.paths.absolute').'/'.$parent->get('slug').'/views';
        //    var_dump($parentLocation);
            app('view.finder')->prependLocation($parentLocation);
        }

        //register selectted theme view path if exists
        $themeLocation = config('themes.paths.absolute').'/'.$theme->get('slug').'/views';
        // var_dump($themeLocation);
        app('view.finder')->prependLocation($themeLocation);
    }
}
