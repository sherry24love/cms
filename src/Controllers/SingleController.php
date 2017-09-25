<?php
namespace Sherrycin\Cms\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use Sherry\Cms\Models\Single ;

use Encore\Admin\Controllers\ModelForm ;

class SingleController extends BaseController {
	use ModelForm;
	
	public function index() {
		
		return Admin::content(function (Content $content) {
			$content->header( '系统页面' );
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
		return Admin::grid( Single::class, function (Grid $grid) {
			$user = auth()->guard('admin')->user();
			$orgId = $this->orgId();
			$grid->model()->where('org_id' , $user->org_id );
			$grid->id('ID')->sortable();
			$grid->model()->orderBy('id' , 'desc' );
			$grid->title(trans('cms::lang.title'));
			$grid->keyword(trans('cms::lang.keyword'));
	
			$grid->created_at(trans('admin::lang.created_at'));
			$grid->updated_at(trans('admin::lang.updated_at'));
			$grid->disableBatchDeletion();
			$grid->actions( function( $action ){
				$action->disableDelete();
			});
			$grid->filter(function ($filter) {
				$filter->disableIdFilter();
				$filter->like('title', trans('cms::lang.title'));
			});
			$grid->disableExport();
		});
	}
	
	/**
	 * 新增
	 */
	public function create() {
		return Admin::content(function (Content $content) {
			$content->header(trans('cms::lang.singlepage'));
			$content->description(trans('admin::lang.create'));
			$content->body($this->form());
		});
	}
	
	/**
	 * 修改单页面的内容
	 */
	public function edit( $id ) {
		return Admin::content(function (Content $content) use( $id ) {
			$content->header(trans('cms::lang.singlepage'));
			$content->description(trans('admin::lang.edit'));
			$content->body($this->form()->edit( $id ) );
		});
	}
	
	
	protected function form() {
		return Admin::form( Single::class, function ( Form $form) {
			$form->display('id', 'ID');
			$orgId = $this->orgId();
			$form->text('title', trans('cms::lang.title'))->rules('required');
			$form->text('author', trans('cms::lang.author'));
			$form->text('keyword', trans('cms::lang.keyword'));
			$form->text('description', trans('cms::lang.description'));
			$form->image('cover', trans('cms::lang.cover'));
			$form->ueditor('content', trans('cms::lang.content'))->rules('required');
		
			$form->display('created_at', trans('admin::lang.created_at'));
			$form->display('updated_at', trans('admin::lang.updated_at'));
			$form->saving( function( $form ) use ( $orgId ) {
				$form->model()->org_id = (int) $orgId ;
			});
		});
	}
}