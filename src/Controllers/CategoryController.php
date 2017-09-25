<?php
namespace Sherrycin\Cms\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;

use Sherry\Cms\Models\Category ;

use Encore\Admin\Controllers\ModelForm ;
use Sherry\Cms\Models\Posts;

class CategoryController extends BaseController
{
	use ModelForm;
	
	public function index() {
		
		return Admin::content(function(Content $content ){
			$content->header(trans('cms::lang.category'));
			$content->description(trans('admin::lang.list'));
			$content->row( $this->treeView()->render() );
		});
	}
	
	public function create() {
		return Admin::content(function (Content $content) {
			$content->header(trans('cms::lang.category'));
			$content->description(trans('admin::lang.create'));
			$content->row($this->form());
		});
	}
	
	/**
	 * Edit interface.
	 *
	 * @param string $id
	 *
	 * @return Content
	 */
	public function edit($id) {
		return Admin::content(function (Content $content) use ($id) {
			$content->header(trans('cms::lang.category'));
			$content->description(trans('admin::lang.edit'));
	
			$content->row($this->form()->edit($id));
		});
	}
	
	/**
	 * Make a form builder.
	 *
	 * @return Form
	 */
	public function form() {
		return Category::form(function (Form $form) {
			$user = auth()->guard('admin')->user();
			$orgId = $user->org_id ;
			$form->display('id', 'ID');
			$form->select('parent_id', trans('admin::lang.parent_id'))->options( function( ) use( $orgId ) {
				$cate = new Category();
				return $cate->selectOwnTree( $orgId );
			});
			$form->text('name', trans('cms::lang.category'))->rules('required');
			$form->text('keyword', trans('cms::lang.keyword'));
			$form->text('description', trans('cms::lang.description'));
			$form->image('cover', trans('shop::lang.cover'))->rules('required')->help('请上传图片');
			$form->ueditor('content' , trans('cms::lang.content') );
			$form->display('created_at', trans('admin::lang.created_at'));
			$form->display('updated_at', trans('admin::lang.updated_at'));
			$form->saving( function( $form ) use( $orgId ) {
				$form->model()->org_id = $orgId ;
			} );
		});
	}
	
	/**
	 * @return \Encore\Admin\Tree
	 */
	protected function treeView()
	{
		return Category::tree(function (Tree $tree) {
			$user = auth()->guard('admin')->user();
			$orgId = $user->org_id ;
			$tree->query(function( $query ) use( $orgId ) {
				return $query->where('org_id' , $orgId );
			});
			$tree->branch(function ($branch) {
				$payload = "<strong>{$branch['name']}</strong>";
				return $payload;
			});
		});
	}
	
	/**
	 * Redirect to edit page.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function show($id)
	{
		return redirect()->action(
				'\Sherry\cms\Controllers\CategoryController@edit', ['id' => $id]
				);
	}
	
	public function destroy($id)
	{
		$count = Category::where('parent_id' , $id )->count();
		if( $count ) {
			return response()->json([
					'status'  => false,
					'message' => '请先删除下级分类',
			]);
		}
		$count = Posts::where('category_id' , $id )->count();
		if( $count ) {
			return response()->json([
					'status'  => false,
					'message' => '请先删除当前分类的资讯信息',
			]);
		}
		if ($this->form()->destroy($id)) {
			return response()->json([
					'status'  => true,
					'message' => trans('admin::lang.delete_succeeded'),
			]);
		} else {
			return response()->json([
					'status'  => false,
					'message' => trans('admin::lang.delete_failed'),
			]);
		}
	}
}