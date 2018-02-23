<?php

namespace Caffeinated\Themes;

use Illuminate\Support\Collection;
use Caffeinated\Themes\Traits\RegistersViewLocations;
use Illuminate\Contracts\View\Factory as ViewFactory;


class Theme extends Collection
{
    use RegistersViewLocations;
    
    /**
     * @var string
     */
    protected $current;
    
    /**
     * @var string|null
     */
    protected $layout = null;
    
    /**
     * Register and set the currently active theme.
     *
     * @param  string  $theme
     */
    public function set($theme)
    {
       // var_dump("Set Theme $theme");
        $parent=$grandparent =null;
        list($theme, $parent,$grandparent) = $this->resolveTheme($theme);

        if (! $this->isCurrent($theme->get('slug')) and (! is_null($this->getCurrent()))) {
            $this->removeRegisteredLocation($theme);
        }
        //Add $theme, parent, and grandparent view path to viewfinder
        //if($grandparent)
        $this->addRegisteredLocation($theme,$parent,$grandparent);

        $this->setCurrent($theme->get('slug'));
    }
    
    /**
     * Get the absolute path of the given theme file.
     *
     * @param  string  $file
     * @param  string  $theme
     * @return string
     */
    public function absolutePath($file = '', $theme = null)
    {
        if (is_null($theme)) {
            $theme = $this->getCurrent();
        }
        
        return config('themes.paths.absolute')."/$theme/$file";
    }
    
    /**
     * Get the relative path of the given theme file.
     *
     * @param  string  $file
     * @param  string  $theme
     * @return string
     */
    public function path($file = '', $theme = null)
    {
        if (is_null($theme)) {
            $theme = $this->getCurrent();
        }
        
        return config('themes.paths.base')."/$theme/$file";
    }
    
    /**
     * Get the layout property.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
     * Set the layout property.
     *
     * @param  string  $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
    
    /**
     * Set the current theme property.
     *
     * @param  string  $theme
     */
    public function setCurrent($theme)
    {
        $this->current = $theme;
    }
    
    /**
     * Get the current theme property.
     *
     * @return string
     */
    public function getCurrent()
    {
        return $this->current;
    }
    
    /**
     * Determine if the given theme is the currently set theme.
     *
     * @param  string  $theme
     * @return bool
     */
    public function isCurrent($theme)
    {
        return $this->current === $theme;
    }
    
    /**
     * Get the absolute path of the given theme.
     *
     * @param  string  $theme
     * @return string
     */
    public function getAbsolutePath($theme)
    {
        return config('themes.paths.absolute').'/'.$theme;
    }


    public function view($view,$data = [], $mergeData = []){
        #return view($view);


        $factory = app(ViewFactory::class);

//        if (func_num_args() === 0) {
//            return $factory;
//        }

        return $factory->make($view, $data, $mergeData);
    }


    /**
     * Generate a HTML link to the given asset using HTTP for the
     * currently active theme.
     *
     * @return string
     */
    public function asset($asset)
    {
        $segments = explode('::', $asset);
        $theme    = null;
        //This function allows the search of assets in the current theme and it parent (if exists)
        //TODO: Added recursive call to get all ancestors of a theme
        if (count($segments) == 2  ) {
            list($theme, $asset) = $segments;


            if($theme=="Theme"){
                $grandparentLocation=null;
                $parentLocation=null;
                $parent=null;
                $grandparent=null;


                $current         = $this->where('slug', self::getCurrent())->first();
                $currentLocation = config('themes.paths.absolute').'/'.$current->get('slug').'/assets';


                if ($current->has('parent')) {
                    $parent         = $this->where('slug', $current->get('parent'))->first();
                    $parentLocation = config('themes.paths.absolute').'/'.$parent->get('slug').'/assets';


                    if ($parent->has('parent')) {
                        $grandparent         = $this->where('slug', $parent->get('parent'))->first();
                        $grandparentLocation = config('themes.paths.absolute').'/'.$grandparent->get('slug').'/assets';

                    }
                }



                $currentTheme=  $this->getCurrent();
                $parentTheme= $this->get($currentTheme.'::parent');
                $themes=array();
                array_push($themes,$current);
                if($parentLocation!=null)
                    array_push($themes,$parent);
                if($grandparentLocation!=null)
                    array_push($themes,$grandparent);
                foreach($themes as $theme){
                    //Add a
                    $themeAssetURL = url(config('themes.paths.base').'/'.$theme->get('slug') .'/'.config('themes.paths.assets').'/'.$asset);
                    $themeAssetPath= public_path().'/'.config('themes.paths.base').'/'.$theme->get('slug') .'/'.config('themes.paths.assets').'/'.$asset;
                    $assetPossibleLocation[$theme->get('slug')]=[$themeAssetURL,$themeAssetPath];
                }
                foreach($assetPossibleLocation as $location){
                    if(file_exists($location[1])) {
                        #dd($location[0]);
                        return $location[0];
                    }
                }
            }
        } else {
            $asset = $segments[0];
        }
        if (count($segments) == 2) {
            list($theme, $asset) = $segments;
        } else {
            $asset = $segments[0];
        }
        return url(config('themes.paths.base').'/'
            .($theme ?: $this->getCurrent()).'/'
            .config('themes.paths.assets').'/'
            .$asset);
    }
}
