(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-2d0c7e72"],{5331:function(r,o,e){"use strict";e.r(o);e("d9e2");var s={data:function(){var r=this;return{form:{old_password:"",password:"",password_confirmation:""},rules:{old_password:[{required:!0,message:"请输入原密码",trigger:"blur"}],password:[{required:!0,message:"请输入新密码",trigger:"blur"},{min:5,max:18,trigger:"blur",message:"密码长度为6到18位"}],password_confirmation:[{required:!0,message:"请输入新密码",trigger:"blur"},{validator:function(o,e,s){e!==r.form.password?s(new Error("请确认新密码")):s()}}]}}},methods:{onSubmit:function(){var r=this;this.$refs.form.validate((function(o){o&&(r.loading=!0,r.$api.post("api/updatePwd",r.form).then((function(o){o.data&&(r.$message({type:"success",message:"修改成功，请重新登录"}),r.$store.dispatch("user/logout").then((function(){r.$router.push("/login")})))})))}))}}},t=e("2877"),a=Object(t.a)(s,(function(){var r=this,o=r.$createElement,e=r._self._c||o;return e("div",[e("page-header",{attrs:{title:"修改密码",content:"定期修改密码可以提高帐号安全性噢~"}}),e("page-main",[e("el-row",[e("el-col",{attrs:{md:24,lg:12}},[e("el-form",{ref:"form",attrs:{model:r.form,rules:r.rules,"label-width":"120px"}},[e("el-form-item",{attrs:{label:"原密码",prop:"old_password"}},[e("el-input",{attrs:{type:"password",placeholder:"请输入原密码"},model:{value:r.form.old_password,callback:function(o){r.$set(r.form,"old_password",o)},expression:"form.old_password"}})],1),e("el-form-item",{attrs:{label:"新密码",prop:"password"}},[e("el-input",{attrs:{type:"password",placeholder:"请输入原密码"},model:{value:r.form.password,callback:function(o){r.$set(r.form,"password",o)},expression:"form.password"}})],1),e("el-form-item",{attrs:{label:"确认新密码",prop:"password_confirmation"}},[e("el-input",{attrs:{type:"password",placeholder:"请输入原密码"},model:{value:r.form.password_confirmation,callback:function(o){r.$set(r.form,"password_confirmation",o)},expression:"form.password_confirmation"}})],1)],1)],1)],1)],1),e("fixed-action-bar",[e("el-button",{attrs:{type:"primary"},on:{click:r.onSubmit}},[r._v("提交")])],1)],1)}),[],!1,null,null,null);o.default=a.exports}}]);