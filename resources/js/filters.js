/**
 * Vue.js内で汎用的に使うフィルター群
**/


/**
 * テキスト内のURL文字列をリンクに変換する
**/
export function url2Link(str) {
    return str.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
}

/**
 * nl2br
**/
export function nl2br(str) {
    return str.replace(/\n/g, "<br>\n");
}
