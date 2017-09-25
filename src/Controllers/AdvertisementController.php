<?php
namespace Sherrycin\Cms\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use Sherry\Cms\Models\Advertisement as Adv ;
use Sherry\Cms\Models\Advtarget ;

use Encore\Admin\Controllers\ModelForm ;

class AdvertisementController extends BaseController {
	use ModelForm;
	
	public function index() {
		return Admin::content(function (Content $content) {
			$content->header(trans('cms::lang.advertisement'));
			$content->description(trans('admin::lang.list'));
			$content->body($this->grid()->render());
		});
	}
	
	/**
	 * Make a grid builder.
	 *
	 * @return Grid
	 */
	protected function grid()
	{
		return Admin::grid( Adv::class, function (Grid $grid) {
			$grid->id('ID')->sortable();
			if( $this->orgId() ) {
				$grid->model()->where('org_id' , $this->orgId() );
			}
			$grid->model()->orderBy('id' , 'desc');
			$grid->title(trans('cms::lang.title'));
			$grid->column('advtarget.name' , trans('cms::lang.advtarget'))->sortable();
			$grid->cover('封面')->image();
			$grid->start_at(trans('cms::lang.start_at'));
			$grid->end_at(trans('cms::lang.end_at'));
			$grid->sort( trans('cms::lang.sort') )->sortable()->editable();
			$states = [
					'on'  => ['value' => 1, 'text' => trans('cms::lang.yes') , 'color' => 'success'],
					'off' => ['value' => 0, 'text' => trans('cms::lang.no') , 'color' => 'danger'],
			];
			$grid->display( trans('cms::lang.display' ) )->switch($states);
			//$grid->created_at(trans('admin::lang.created_at'));
			//$grid->updated_at(trans('admin::lang.updated_at'));
			$grid->filter(function ($filter) {
				$filter->disableIdFilter();
				$filter->like('title', trans('cms::lang.title'));
				
				$filter->is('target_id' , '广告位')->select(
					Advtarget::pluck('name' , 'id' )			
				);
				
			});
			$grid->disableExport();
		});
	}
	
	/**
	 * 新增
	 */
	public function create() {
		return Admin::content(function (Content $content) {
			$content->header(trans('cms::lang.advertisement'));
			$content->description(trans('admin::lang.create'));
			$content->body($this->form());
		});
	}
	
	/**
	 * 修改
	 */
	public function edit( $id ) {
		return Admin::content(function (Content $content) use( $id ) {
			$content->header(trans('cms::lang.advertisement'));
			$content->description(trans('admin::lang.edit'));
			$content->body($this->form()->edit( $id ) );
		});
	}
	
	
	protected function form() {
		return Admin::form( Adv::class, function ( Form $form) {
			$form->display('id', 'ID');
		
			$form->text('title', trans('cms::lang.title'))->rules('required');
			$orgId = $this->orgId();
			$form->select('target_id', trans('cms::lang.advtarget'))->options(function() use( $orgId ) {
				return Advtarget::pluck('name' , 'id');
			})->rules('required');
			$form->text('link' , trans('cms::lang.link'))->help( $this->linkHelper() )->rules('required');
			$form->image('cover', trans('cms::lang.cover'))->rules('required');
			$form->dateRange('start_at', 'end_at' , trans('cms::lang.adv_display_date') );
			$form->number('sort', trans('cms::lang.sort'))->default( 0 )->help('序号不能为负数，且序号越大越靠前');
			$states = [
					'on'  => ['value' => 1, 'text' => trans('cms::lang.yes') , 'color' => 'success'],
					'off' => ['value' => 0, 'text' => trans('cms::lang.no') , 'color' => 'danger'],
			];
			$form->switch( 'display' , trans('cms::lang.display' ) )->states($states);
			$form->display('created_at', trans('admin::lang.created_at'));
			$form->display('updated_at', trans('admin::lang.updated_at'));
			$form->saving( function( $form) {
				$form->model()->org_id = 0;
			});
		});
	}
	
	
	protected function linkHelper() {
		return '广告地址规则';
	}
}