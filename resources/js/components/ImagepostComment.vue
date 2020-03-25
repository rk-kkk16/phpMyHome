<template>
<!-- <div id="cmnt-area" class="card"><div class="card-body"> -->
<div id="cmnt-area">

    <div v-bind:class="{ 'hidden':!before_exists }">
        <button @click="loadBefore();" class="btn btn-link" style="width:100%;">
            <p style="background-color:#cfcfcf; padding:5px; text-align:center">前の{{num}}件</p>
        </button>
    </div>
    <div v-bind:class="{ 'hidden':!page_end }">
        <p style="background-color:#ffffef; padding:5px; text-align:center">全件表示しました。</p>
    </div>

    <ul v-if="cmnts.length > 0" class="list-group" style="margin-bottom:1em;">
        <li class="list-group-item" v-for="cmnt in cmnts">
            <div class="cmnt-li-left">
                <span class="profimg prfmini" v-bind:style="`background-image:url(/users/icon/${cmnt.user_id})`"></span>
            </div>
            <div class="cmnt-li-right">
                <p class="cmnt-body" v-html="$options.filters.url2Link(cmnt.comment)"></p>
                <p class="cmnt-delbtn">
                    <button v-if="cmnt.user_id == myuser_id" class="btn btn-secondary" style="padding:2px 10px" v-bind:onclick="`mwOpen('mw_cmntdelete','mw_cmntdelete',{id:${cmnt.id}})`"><i class="fas fa-trash"></i></button>
                </p>
                <br clear="both">
                <p style="text-align:right">
                    <i class="fas fa-calendar-alt"></i> {{cmnt.created_at|moment}}
                </p>
            </div>
            <br clear="both">
        </li>
    </ul>
    <p v-else>コメントはありません。</p>

    <div class="form-group row">

        <div class="col-md-10 col-sm-12">
        <textarea class="form-control" id="cmntform" placeholder="コメントを入力"></textarea>
        </div>

        <div class="col-md-2 col-sm-12 text-right" style="padding-top:10px;">
            <button type="button" class="btn btn-primary" @click="sendComment()">送信</button>
        </div>
    </div>
<!-- </div></div> -->
</div>
</template>
<script>
import moment from 'moment';

export default {
    filters: {
        moment: function(date) {
            return moment(date).format('YYYY/MM/DD HH:mm');
        },
    },

    data() {
        return {
            cmnts: [],
            index: {},
            page: 1,
            total: 0,
            page_end: false,
            before_exists: false,
        };
    },

    props: ['num', 'imagepost_id', 'myuser_id'],

    mounted() {
        this.loadBefore();
    },

    methods: {
        loadBefore() {
            $('#loading').removeClass('hidden');
            let self = this;
            let url = '/api/imagepost/comment/list/' + self.imagepost_id + '?page=' + self.page + '&num=' + self.num;
            axios.get(url)
            .then(function(response) {
                self.total = response.data.meta.total;
                for (var i = 0; i < response.data.data.length; i++) {
                    let cmnt = response.data.data[i];
                    if (typeof(self.index[cmnt.id]) == 'undefined') {
                        self.cmnts.unshift(cmnt);
                        self.index[cmnt.id] = cmnt.id;
                    }
                }

                if (response.data.meta.last_page == 1) {
                    self.before_exists = false;
                }
                else {
                    if (response.data.meta.last_page > self.page) {
                        self.before_exists = true;
                        self.page++;
                    } else {
                        self.before_exists = false;
                        self.page_end = true;
                    }
                }
                $('#loading').addClass('hidden');
            })
            .catch(error => {
                console.error(error);
                $('#loading').addClass('hidden');
            });
        },

        sendComment() {
            if (!$('#cmntform').val()) {
                return;
            }

            let self = this;
            let url = '/api/imagepost/comment/add';
            let params = {
                imagepost_id: self.imagepost_id,
                comment: $('#cmntform').val(),
            };
            $('#loading').removeClass('hidden');
            axios.post(url, params)
            .then(function(response) {
                if (typeof(response.data.errors) == 'undefined') {
                    $('#cmntform').val('');
                    let cmnt = response.data.data;
                    self.index[cmnt.id] = cmnt.id;
                    self.cmnts.push(cmnt);
                    toastr.success('コメントしました。');
                } else {
                    if (typeof(response.data.errors.comment) != 'undefined') {
                        toastr.error(response.data.errors.comment);
                    }
                }
                $('#loading').addClass('hidden');
            })
            .catch(error => {
                console.error(error);
                $('#loading').addClass('hidden');
            });
        },

        deleteComment(cmnt_id) {
            let self = this;
            let url = '/api/imagepost/comment/delete/' + cmnt_id;
            $('#loading').removeClass('hidden');
            axios.delete(url)
            .then(function(response) {
                $('#loading').addClass('hidden');
                if (typeof(response.data.errors) != 'undefined') {
                    toastr.error('エラーが発生しました。');
                    return;
                }
                let delcmnt = response.data.data;
                delete self.index[delcmnt.id];
                for (var i = 0; i < self.cmnts.length; i++) {
                    if (self.cmnts[i].id == delcmnt.id) {
                        self.cmnts.splice(i, 1);
                        break;
                    }
                }
                toastr.success('コメントを削除しました。');
            })
            .catch(error => {
                console.error(error);
                $('#loading').addClass('hidden');
            });
        }
    }
}
</script>
<style scoped>
.cmnt-li-left {
    float: left;
    width: 40px;
    text-align: center;
}
.cmnt-li-right {
    float: right;
    width: calc(100% - 50px);
    text-align: left;
}
.cmnt-body {
    float:left;
    width:calc(100% - 50px);
    text-align:left;
}
.cmnt-delbtn {
    float:right;
    width: 40px;
    text-align:right;
}
</style>
