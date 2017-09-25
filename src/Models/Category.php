<?php

namespace Sherrycin\Cms\Models;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Category extends Model {
	use ModelTree, AdminBuilder;
	
	protected $table='cms_category';
	
	
    protected $fillable = [
    		'name' , 'keyword' , 'description' , 'order' , 'parent_id' , 'cover'
    ];

    public function __construct(array $attributes = []) {
    	
    	$this->setTitleColumn('name') ;
    	parent::__construct( $attributes );
    }
    
    
    public function selectTree() {
    	$options = (new static())->buildSelectOptions();
    	
    	return collect($options)->all();
    }
    
    public function selectOwnTree( $orgId ) {
    	$this->withQuery(function( $query) use( $orgId ) {
    		return $query->where('org_id' , $orgId );
    	});
    	$options = $this->buildSelectOptions();
    	return collect($options)->prepend('请选择资讯分类', 0)->all();
    }
}
