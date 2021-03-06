<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\User;
use App\Models\ScrapEntry;
use App\Models\ScCategory;
use App\Models\ScFile;
use App\Models\ScGoodTrx;
use Storage;
use Validator;
use App\Support\Markdown;
use Illuminate\Support\Facades\Config;


class ScrapController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    // index
    public function index(Request $request) {
        $posts = null;
        $num = 10;
        // 検索条件
        $keyword = $request->input('keyword') ?: null;
        $ord = ($request->input('ord') == 'good_point') ? 'good_point' : 'id';
        $category_id = $request->input('category_id') ?: null;
        $page = $request->input('page') ?: 1;
        $nowcategory = null;
        if ($category_id) {
            $nowcategory = ScCategory::find($category_id);
        }

        if ($keyword || $nowcategory) {
            $query = ScrapEntry::query();
            if ($keyword) {
                $keyword = trim($keyword);
                $query->where(function($query) use ($keyword) {
                    $query->where('body', 'LIKE', '%'.$keyword.'%');
                    $query->orWhere('subject', 'LIKE', '%'.$keyword.'%');
                });
            }
            if ($nowcategory && !$nowcategory->is_primary) {
                $category_ids = [$nowcategory->id];
                // 指定カテゴリの子カテゴリも検索対象
                if (count($nowcategory->childs)) {
                    foreach ($nowcategory->childs as $child) {
                        $category_ids[] = $child->id;
                    }
                }
                $query->whereIn('sc_category_id', $category_ids);
            }
            if ($ord == 'good_point') {
                $posts = $query->orderBy('good_point', 'desc')->orderBy('id', 'desc')->paginate($num);
            } else {
                $posts = $query->orderBy('id', 'desc')->paginate($num);
            }
        } else {
            if ($ord == 'good_point') {
                $posts = ScrapEntry::query()->orderBy('good_point', 'desc')->orderBy('id', 'desc')->paginate($num);
            } else {
                $posts = ScrapEntry::query()->orderBy('id', 'desc')->paginate($num);
            }
        }

        // postごとのdescriptionを作成
        $descriptions = [];
        foreach ($posts as $post) {
            $desc = ['type' => 'text', 'data' => null];
            if (count($post->files)) {
                foreach ($post->files as $scfile) {
                    if ($scfile->is_image) {
                        $desc['type'] = 'image';
                        $desc['data'] = $scfile;
                        break;
                    }
                }
            }
            else if (preg_match('/(https?:\/\/[^\s]+)/', $post->body, $mat)) {
                $url = $mat[1];
                $desc['type'] = 'link';
                $desc['data'] = $url;
            } else {
                $desc['data'] = mb_substr($post->body, 0, 30) . '…';
            }
            $descriptions[$post->id] = $desc;
        }

        $params = ['ord' => $ord];
        if ($nowcategory) {
            $params['category_id'] = $nowcategory->id;
        }
        if ($keyword) {
            $params['keyword'] = $keyword;
        }

        // 並べ替え,投稿,詳細ページで引き回すためのパラメータ文字列
        $paramstrs = $this->makeParamStrs($params, $page, true);

        return view('app.scrap.index', [
                    'posts' => $posts,
                    'descriptions' => $descriptions,
                    'nowcategory' => $nowcategory,
                    'category_id' => $category_id,
                    'keyword' => $keyword,
                    'params' => $params,
                    'paramstr' => $paramstrs['paramstr'],
                    'in_paramstr' => $paramstrs['in_paramstr'],
                    'categorys' => ScCategory::query()->where('depth', 1)->get(),
                ]);
    }

    // 投稿詳細表示
    public function detail(Request $request) {
        $id = $request->id;
        // 引継ぎパラメータ
        $in_page = ($request->input('in_page')) ?: null;
        $in_category_id = ($request->input('in_category_id')) ?: null;
        $in_keyword = ($request->input('in_keyword')) ?: null;
        $in_ord = ($request->input('in_ord')) ?: null;

        $post = ScrapEntry::findOrfail($id);
        $bodyhtml = $post->body; //Markdown::parse($post->body);

        // 前後の投稿取得
        // 前画面から引き継いだカテゴリなどの条件を適用する
        // keyword,category_idを適用する ordは難しいのでひとまずパス
        $query_next = ScrapEntry::query();
        $query_before = ScrapEntry::query();
        if ($in_keyword) {
            $keyword = trim($in_keyword);
            $query_before->where(function($query_before) use ($keyword) {
                $query_before->where('body', 'LIKE', '%'.$keyword.'%');
                $query_before->orWhere('subject', 'LIKE', '%'.$keyword.'%');
            });
            $query_next->where(function($query_next) use ($keyword) {
                $query_next->where('body', 'LIKE', '%'.$keyword.'%');
                $query_next->orWhere('subject', 'LIKE', '%'.$keyword.'%');
            });
        }

        $nowcategory = null;
        if ($in_category_id) {
            $nowcategory = ScCategory::find((int)$in_category_id);
        }
        if ($nowcategory && !$nowcategory->is_primary) {
            $category_ids = [$nowcategory->id];
            // 指定カテゴリの子カテゴリも検索対象
            if (count($nowcategory->childs)) {
                foreach ($nowcategory->childs as $child) {
                    $category_ids[] = $child->id;
                }
            }
            $query_before->whereIn('sc_category_id', $category_ids);
            $query_next->whereIn('sc_category_id', $category_ids);
        }

        $query_next->where('id', '>', $id)->orderBy('id', 'asc');
        $query_before->where('id', '<', $id)->orderBy('id', 'desc');
        $nextpost = $query_next->limit(1)->first();
        $beforepost = $query_before->limit(1)->first();

        // アップロードファイルの仕訳
        $imgs = array();
        $files = array();
        if (count($post->files) > 0) {
            foreach ($post->files as $scfile) {
                if ($scfile->is_image) {
                    $imgs[] = $scfile;
                } else {
                    $files[] = $scfile;
                }
            }
        }

        $params = [
            'category_id' => $in_category_id,
            'keyword' => $in_keyword,
            'ord' => $in_ord,
        ];
        $paramstrs = $this->makeParamStrs($params, $in_page);

        return view('app.scrap.detail', [
            'post' => $post,
            'bodyhtml' => $bodyhtml,
            'beforepost' => $beforepost,
            'nextpost' => $nextpost,
            'imgs' => $imgs,
            'files' => $files,
            'thumbs' => Config::get('app_scrap.thumbs'),
            'in_paramstr' => $paramstrs['in_paramstr'],
            'paramstr' => $paramstrs['paramstr'],
        ]);
    }

    // add view
    public function add(Request $request) {
        // 引継ぎパラメータ
        $in_page = ($request->input('in_page')) ?: null;
        $in_category_id = ($request->input('in_category_id')) ?: null;
        $in_keyword = ($request->input('in_keyword')) ?: null;
        $in_ord = ($request->input('in_ord')) ?: null;

        $params = [
            'category_id' => $in_category_id,
            'keyword' => $in_keyword,
            'ord' => $in_ord,
        ];
        $paramstrs = $this->makeParamStrs($params, $in_page);

        $post = new ScrapEntry();
        $subject = old('subject', '');
        $body = old('body', '');
        $sc_category_id = old('sc_category_id', '');
        $uploads = old('upload', []);
        $categorys = ScCategory::query()->where('depth', 1)->get();

        $scfile_delchks = old('delfile', []);

        return view('app.scrap.edit', [
            'post' => $post,
            'subject' => $subject,
            'body' => $body,
            'sc_category_id' => $sc_category_id,
            'uploads' => $uploads,
            'categorys' => $categorys,
            'max_upload_file' => 5,
            'scfile_delchks' => $scfile_delchks,
            'in_paramstr' => $paramstrs['in_paramstr'],
            'paramstr' => $paramstrs['paramstr'],
        ]);
     }

    // edit view
    public function edit(Request $request) {
        // 引継ぎパラメータ
        $in_page = ($request->input('in_page')) ?: null;
        $in_category_id = ($request->input('in_category_id')) ?: null;
        $in_keyword = ($request->input('in_keyword')) ?: null;
        $in_ord = ($request->input('in_ord')) ?: null;

        $params = [
            'category_id' => $in_category_id,
            'keyword' => $in_keyword,
            'ord' => $in_ord,
        ];
        $paramstrs = $this->makeParamStrs($params, $in_page);

        // 自分以外の投稿でも編集可能に 投稿が存在しない場合は404
        $post = ScrapEntry::query()->where('id', $request->id)->first();
        if (!$post) {
            return \App::abort(404);
        }

        $subject = old('subject', $post->subject);
        $body = old('body', $post->body);
        $sc_category_id = old('sc_category_id', $post->sc_category_id);
        $uploads = old('upload', []);
        $categorys = ScCategory::query()->where('depth', 1)->get();

        $scfile_delchks = old('delfile', []);

        return view('app.scrap.edit', [
            'post' => $post,
            'subject' => $subject,
            'body' => $body,
            'sc_category_id' => $sc_category_id,
            'uploads' => $uploads,
            'categorys' => $categorys,
            'max_upload_file' => 5,
            'scfile_delchks' => $scfile_delchks,
            'paramstr' => $paramstrs['paramstr'],
            'in_paramstr' => $paramstrs['in_paramstr'],
        ]);
    }


    // 編集投稿 validate=>save
    public function validateEdit(Request $request) {
        $id = $request->id;
        // 投稿が存在しない場合は404
        $post = ScrapEntry::query()->where('id', $id)->first();
        if (!$post) {
            return \App::abort(404);
        }

        // 引継ぎパラメータ
        $in_page = ($request->input('in_page')) ?: null;
        $in_category_id = ($request->input('in_category_id')) ?: null;
        $in_keyword = ($request->input('in_keyword')) ?: null;
        $in_ord = ($request->input('in_ord')) ?: null;

        $params = [
            'category_id' => $in_category_id,
            'keyword' => $in_keyword,
            'ord' => $in_ord,
        ];
        $paramstrs = $this->makeParamStrs($params, $in_page);


        $validate_opts = [
            'subject' => 'required|string|max:100',
            'body' => 'required|string|max:10000',
            'sc_category_id' => 'required|integer|exists:sc_categories,id',
        ];
        // ファイルアップロードがあった場合
        $has_upload = false;
        if (isset($request->upload) && is_array($request->upload)) {
            $has_upload = true;
            $validate_opts['upload.*'] = [
                'required',
                'string',
                function($attribute, $value, $fail) {
                    // file_path;file_name;is_image(0|1)
                    $ary = explode(';', $value);
                    if (count($ary) !== 3 || ((int)$ary[2] !== 0 && (int)$ary[2] !== 1)) {
                        return $fail('アップロードファイルのデータが不正です。');
                    }
                    $disk = Storage::disk('public');
                    $file_path = preg_replace('|^/storage/|', '', $ary[0]);
                    if (!$disk->exists($file_path)) {
                        return $fail('アップロードファイルが存在しません。');
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $validate_opts);
        if ($validator->fails()) {
            return redirect('/scrap/edit/' . $id . '?'. $paramstrs['in_paramstr'])->withErrors($validator)->with('error', '入力内容に不備があります。')->withInput();
        }

        // ScFile削除指定のチェック
        $delfiles = $request->delfile ?: [];
        if (count($delfiles) > 0) {
            foreach ($delfiles as $delfile_id) {
                $del_scfile = ScFile::find($delfile_id);
                if (!$del_scfile) {
                    return redirect('/scrap/edit/' . $id . '?' . $paramstrs['in_paramstr'])->with('error', '削除ファイルの指定が不正です。')->withInput();
                }
            }
        }

        // 編集の保存
        $post->subject = $request->subject;
        $post->body = $request->body;
        $post->sc_category_id = $request->sc_category_id;
        $post->save();

        // ファイルの移動とScFile登録
        if ($has_upload) {
            $disk = Storage::disk('public');
            foreach ($request->upload as $upload) {
                list($file_path, $file_name, $is_image) = explode(';', $upload);

                $file_path = preg_replace('|^/storage/|', '', $file_path);

                $file_type = preg_replace('/^.*\.([^\.]+)$/', '\1', $file_path);
                if (!$file_name) {
                    $file_name = date('YmdHis') . microtime(true) . '.' . $file_type;
                }
                $file_size = filesize($disk->path($file_path));
                $scfile = new ScFile();
                $scfile->user_id = $post->user_id;
                $scfile->scrap_entry_id = $post->id;

                $id_range_a = (int)($post->id / 100) + 1;
                $id_range_b = $id_range_a + 99;
                $scfile->id_range = $id_range_a . '-' . $id_range_b;

                $scfile->file_type = $file_type;
                $scfile->file_name = $file_name;
                $scfile->file_size = $file_size;
                $scfile->is_image = ($is_image);
                $scfile->save();
                // 保存パス scrap/id-range/entry_id/file_id.file_type
                $save_path = 'scrap/' . $scfile->id_range . '/' . $scfile->scrap_entry_id . '/' . $scfile->id . '.' . $scfile->file_type;
                $disk->move($file_path, $save_path);
            }
        }

        // scfile削除
        if (count($delfiles) > 0) {
            $disk = Storage::disk('public');
            foreach ($delfiles as $delfile_id) {
                $del_scfile = ScFile::find($delfile_id);
                $del_file_path = 'scrap/' . $del_scfile->id_range . '/' . $del_scfile->scrap_entry_id . '/' . $del_scfile->id . '.' . $del_scfile->file_type;
                $del_scfile->delete();
                $disk->delete($del_file_path);
            }
        }

        return redirect('/scrap/' . $post->id . '?'. $paramstrs['in_paramstr'])->with('status', '投稿の編集を保存しました。');
    }

    // 新規投稿 validate=>create
    public function validateAdd(Request $request) {
        // 引継ぎパラメータ
        $in_page = ($request->input('in_page')) ?: null;
        $in_category_id = ($request->input('in_category_id')) ?: null;
        $in_keyword = ($request->input('in_keyword')) ?: null;
        $in_ord = ($request->input('in_ord')) ?: null;

        $params = [
            'category_id' => $in_category_id,
            'keyword' => $in_keyword,
            'ord' => $in_ord,
        ];
        $paramstrs = $this->makeParamStrs($params, $in_page);

        $validate_opts = [
            'subject' => 'required|string|max:100',
            'body' => 'required|string|max:10000',
            'sc_category_id' => 'required|integer|exists:sc_categories,id',
        ];
        // ファイルアップロードがあった場合
        $has_upload = false;
        if (isset($request->upload) && is_array($request->upload)) {
            $has_upload = true;
            $validate_opts['upload.*'] = [
                'required',
                'string',
                function($attribute, $value, $fail) {
                    // file_path;file_name;is_image(0|1)
                    $ary = explode(';', $value);
                    if (count($ary) !== 3 || ((int)$ary[2] !== 0 && (int)$ary[2] !== 1)) {
                        return $fail('アップロードファイルのデータが不正です。');
                    }
                    $disk = Storage::disk('public');
                    $file_path = preg_replace('|^/storage/|', '', $ary[0]);
                    if (!$disk->exists($file_path)) {
                        return $fail('アップロードファイルが存在しません。');
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $validate_opts);
        if ($validator->fails()) {
            return redirect('/scrap/add'. '?' . $paramstrs['in_paramstr'])->withErrors($validator)->with('error', '入力内容に不備があります。')->withInput();
        }

        // ScrapEntry登録
        $entry = new ScrapEntry();
        $entry->subject = $request->subject;
        $entry->body = $request->body;
        $entry->sc_category_id = $request->sc_category_id;
        $entry->user_id = Auth::user()->id;
        $entry->save();

        // ファイルの移動とScFile登録
        if ($has_upload) {
            $disk = Storage::disk('public');
            foreach ($request->upload as $upload) {
                list($file_path, $file_name, $is_image) = explode(';', $upload);

                $file_path = preg_replace('|^/storage/|', '', $file_path);

                $file_type = preg_replace('/^.*\.([^\.]+)$/', '\1', $file_path);
                if (!$file_name) {
                    $file_name = date('YmdHis') . microtime(true) . '.' . $file_type;
                }
                $file_size = filesize($disk->path($file_path));
                $scfile = new ScFile();
                $scfile->user_id = $entry->user_id;
                $scfile->scrap_entry_id = $entry->id;

                $id_range_a = (int)($entry->id / 100) + 1;
                $id_range_b = $id_range_a + 99;
                $scfile->id_range = $id_range_a . '-' . $id_range_b;

                $scfile->file_type = $file_type;
                $scfile->file_name = $file_name;
                $scfile->file_size = $file_size;
                $scfile->is_image = ($is_image);
                $scfile->save();

                // 保存パス scrap/id-range/entry_id/file_id.file_type
                $save_path = 'scrap/' . $scfile->id_range . '/' . $scfile->scrap_entry_id . '/' . $scfile->id . '.' . $scfile->file_type;
                $disk->move($file_path, $save_path);
            }
        }

        return redirect('/scrap/?' . $paramstrs['paramstr'])->with('status', '投稿を追加しました。');
    }


    // 投稿削除
    public function delete(Request $request) {
        // 引継ぎパラメータ
        $in_page = ($request->input('in_page')) ?: null;
        $in_category_id = ($request->input('in_category_id')) ?: null;
        $in_keyword = ($request->input('in_keyword')) ?: null;
        $in_ord = ($request->input('in_ord')) ?: null;

        $params = [
            'category_id' => $in_category_id,
            'keyword' => $in_keyword,
            'ord' => $in_ord,
        ];
        $paramstrs = $this->makeParamStrs($params, $in_page);

        $id = $request->id;
        // 自分の投稿でない場合は404
        $post = ScrapEntry::query()->where('id', $id)->where('user_id', Auth::user()->id)->first();
        if (!$post) {
            return \App::abort(404);
        }

        $scfiles = $post->files;

        $post->delete();

        // 関連するScComment ScFile ScGoodTrxを削除
        $goodtrxs = ScGoodTrx::query()->where('scrap_entry_id', $id)->get();
        foreach ($goodtrxs as $goodtrx) {
            $goodtrx->delete();
        }

        // ScFileの削除 実ファイルも削除する
        $disk = Storage::disk('public');
        foreach ($scfiles as $scfile) {
            $del_file_path = 'scrap/' . $scfile->id_range . '/' . $scfile->scrap_entry_id . '/' . $scfile->id . '.' . $scfile->file_type;
            $scfile->delete();
            $disk->delete($del_file_path);
        }

        return redirect('/scrap/?' . $paramstrs['paramstr'])->with('status', '投稿を削除しました。');
    }


    // カテゴリ管理
    public function categories(Request $request) {
        $categorys = ScCategory::query()->where('depth', 1)->get();
        return view('app.scrap.categories', [
            'categories' => $categorys,
        ]);
    }

    // カテゴリ削除
    public function deleteCategory(Request $request) {
        $id = (int)$request->id ?: null;
        $category = ScCategory::find($id);
        $delok = false;
        if ($category) {
            // 属する投稿の有無を確認 存在する場合は削除不可
            $query = ScrapEntry::query();
            $query->where('sc_category_id', $id);
            $entry = $query->limit(1)->first();
            if (!$entry) {
                $delok = true;
            }
        }

        if ($delok) {
            $category->delete();
            return redirect('/scrap/categories')->with('status', 'カテゴリを削除しました。');
        } else {
            return redirect('/scrap/categories')->with('error', '投稿のあるカテゴリは削除できません。');
        }
    }

    // カテゴリ編集
    public function validateEditCategory(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:30',
            'id' => 'required|integer|exists:sc_categories,id',
        ]);
        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('category_name')) {
                $errors['category_name'] = $validator->errors()->first('category_name');
            }
            $errmsg = implode("\n", $errors);
            return redirect()->with('error', $errmsg);
        }
        $category = ScCategory::find($request->id);
        $category->category_name = $request->category_name;
        $category->save();
        return redirect('/scrap/categories')->with('status', '変更を保存しました。');
    }

    // カテゴリ追加
    public function validateAddCategory(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:30',
            'parent_category_id' => 'nullable|integer|exists:sc_categories,id',
        ]);

        if ($validator->fails()) {
            $errors = [];
            if ($validator->errors()->has('category_name')) {
                $errors['category_name'] = $validator->errors()->first('category_name');
            }
            if ($validator->errors()->has('parent_category_id')) {
                $errors['parent_category_id'] = $validator->errors()->first('parent_category_id');
            }
            $errmsg = implode("\n", $errors);
            return redirect()->with('error', $errmsg);
        }
        $category = new ScCategory();
        $category->category_name = $request->category_name;
        $category->user_id = Auth::user()->id;
        if ($request->parent_category_id) {
            $category->parent_category_id = $request->parent_category_id;
            $category->depth = 2;
        }
        $category->save();
        return redirect('/scrap/categories')->with('status', 'カテゴリを追加しました。');
    }


    // パラメータ引き回し用文字列の生成
    // index_mode: trueの場合paramstrにはord,pageを引き継がない
    private function makeParamStrs($params, $in_page, $index_mode = false) {
        $paramstr = $in_paramstr = $p = '';
        foreach ($params as $pk => $pv) {
            if (!$pv) {
                continue;
            }
            if ($index_mode) {
                if ($pk != 'ord') {
                    $paramstr .= $p . $pk . '=' .urlencode($pv);
                }
            } else {
                $paramstr .= $p . $pk . '=' .urlencode($pv);
            }

            $in_paramstr .= $p . 'in_' . $pk . '=' . urlencode($pv);
            $p = '&';
        }
        if ($in_page && !$index_mode) {
            $paramstr .= $p . 'page=' . (int)$in_page;
        }
        $in_paramstr .= $p . 'in_page=' . (int)$in_page;
        return [
            'paramstr' => $paramstr,
            'in_paramstr' => $in_paramstr,
        ];
    }
}
