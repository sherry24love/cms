<?php
namespace Sherrycin\Cms\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Column;

use Sherrycin\Cms\Models\Category ;
use Sherrycin\Cms\Models\Posts ;

use Encore\Admin\Controllers\ModelForm ;
use App\Admin\Extensions\Tools\Trashed;

class PostsController extends BaseController {
	use ModelForm;
	
	public function index() {
		
		return Admin::content(function (Content $content) {
			$content->header(trans('cms::lang.posts'));
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
		return Admin::grid( Posts::class, function (Grid $grid) {
			$grid->id('ID')->sortable();
			$grid->model()->orderBy('id' , 'desc');
			if (request('trash') == 1) {
				$grid->model()->onlyTrashed();
			}
			//如果是平台管理员则显示所有
			$user = auth()->guard('admin')->user();
			$orgId = $this->orgId();
			if( $orgId ) {
				//$grid->model()->where('org_id' , $orgId );
			}
			/**
			 * 如果是平台管理员过来 必须有session('org_id') 否则直接视为平台菜单
			$orgId = $user->org_id ;
			if( $orgId && $orgId != 0 ) {
				$grid->model()->where('org_id' , $orgId );
			}
			**/
			
			$grid->title(trans('cms::lang.title'));
			//$grid->column('category.name' , trans('cms::lang.category'));
			$states = [
					'on'  => ['value' => 1, 'text' => trans('cms::lang.yes') , 'color' => 'success'],
					'off' => ['value' => 0, 'text' => trans('cms::lang.no') , 'color' => 'danger'],
			];
			if (request('trash') != 1) {
				$grid->is_hot( trans('cms::lang.is_hot' ) )->switch($states);
				$grid->is_recom( trans('cms::lang.is_recom' ) )->switch($states);
				$grid->is_top( trans('cms::lang.is_top' ) )->switch($states);
				$grid->is_pic( trans('cms::lang.is_pic' ) )->switch($states);
				$user = auth()->guard('admin')->user();
				if( !$user->org_id ) {
					$grid->display('是否显示')->switch( $states );
				}
			}
			$grid->created_at(trans('admin::lang.created_at'));
			$grid->filter(function ($filter) {
				$filter->like('title', trans('cms::lang.title'));
				/**
				$filter->equal('category_id' , trans('cms::lang.category') )->select( function(){
					$cate = new Category();
					/**
					 if( $orgId ) {
					 return $cate->selectOwnTree( $orgId );
					 } else {
					 return $cate->selectOwnTree( 0 );
					 }
					 **
					//这里目前只开放平台分类，因些类别根目录为0
					return $cate->selectOwnTree( 0 );
				});
			**/
				$filter->disableIdFilter();
			});
			$grid->tools( function( $tools ){
				$tools->append( new Trashed() );
			});
			$grid->disableBatchDeletion();
			$grid->disableExport();
			$grid->actions( function( $action ) use( $user ) {
				if( $user->org_id && $action->row->display == 1 ) {
					$action->disableDelete();
				}
				if (request('trash') == 1) {
					$action->disableEdit();
				}
			});
		});
	}
	
	/**
	 * 新增
	 */
	public function create() {
		return Admin::content(function (Content $content) {
			$content->header(trans('cms::lang.posts'));
			$content->description(trans('admin::lang.create'));
			$content->body($this->form());
		});
	}
	
	/**
	 * 修改单页面的内容
	 */
	public function edit( $id ) {
		return Admin::content(function (Content $content) use( $id ) {
			$content->header(trans('cms::lang.posts'));
			$content->description(trans('admin::lang.create'));
			$content->body($this->form()->edit( $id ) );
		});
	}
	
	public function update( $id ) {
		$form = $this->form();
		
		//将接下来的指令设置为 edit 模式  省掉一些由于必填带来的错误 
		$form->builder()->setMode('edit');
		return $form->update( $id );
	}
	
	
	protected function form() {
		return Admin::form( Posts::class, function ( Form $form) {
			$form->display('id', 'ID');
			$orgId = $this->orgId() ;
			$form->text('title', trans('cms::lang.title'))->rules('required')
			->vmessages([
					'required' => '请填写资讯标题'
			])
			->mustFill();
			$form->select('category_id', trans('cms::lang.category'))->options(function() use( $orgId ) {
				$cate = new Category();
				/**
				if( $orgId ) {
					return $cate->selectOwnTree( $orgId );
				} else {
					return $cate->selectOwnTree( 0 );
				}
				**/
				//这里目前只开放平台分类，因些类别根目录为0
				return $cate->selectOwnTree( 0 );
				
			})->help( trans('cms::lang.category_help'))
			->rules('required')
			->vmessages([
					'required' => '请选择资讯分类' ,
			])->mustFill();
			$form->text('author', trans('cms::lang.author'));
			$form->text('keyword', trans('cms::lang.keyword'));
			$form->text('description', trans('cms::lang.description'));
			$form->image('cover', trans('cms::lang.cover'));
			$form->ueditor('content', trans('cms::lang.content'))->rules('required');
			$states = [
					'on'  => ['value' => 1, 'text' => trans('cms::lang.yes') , 'color' => 'success'],
					'off' => ['value' => 0, 'text' => trans('cms::lang.no') , 'color' => 'danger'],
			];
			$form->switch( 'is_hot' , trans('cms::lang.is_hot'))->states( $states );
			$form->switch( 'is_recom' , trans('cms::lang.is_recom'))->states( $states );
			$form->switch( 'is_top' , trans('cms::lang.is_top'))->states( $states );
			$form->switch( 'is_pic' , trans('cms::lang.is_pic'))->states( $states );
			$user = auth()->guard('admin')->user();
			if( !$user->org_id ) {
				$form->switch( 'display' , '立即显示')->states( $states );
			}
			
			
			$form->saving( function( $form ) use ( $orgId ) {
				
				if( $form->builder()->isMode( 'create' ) ) {
					$form->model()->org_id = $orgId ? $orgId : 0  ;
				}
			});
		});
	}
	
	/**
	 * 从回收站中移出
	 * @param unknown $id
	 */
	public function restore( $id ) {
		$post = Posts::onlyTrashed()->find( $id );
		if( $post->restore() ) {
			return response()->json([
					'status'  => true ,
					'message' => '恢复完成',
			]);
		} else {
			return response()->json([
					'status'  => false,
					'message' => '恢复失败',
			]);
		}
	}
}