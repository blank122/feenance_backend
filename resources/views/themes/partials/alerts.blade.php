@if(session('errorMessage'))
<script>
	$(function() {
		var Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 3000
		});
		Toast.fire({
			type: 'error',
			title: '&nbsp;&nbsp;&nbsp; ALERT! {!! session('errorMessage') !!} &nbsp'
		})
	});
</script>
@endif
@if(session('successMessage'))
<script>
	$(function() {
		var Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 3000
		});
		Toast.fire({
			type: 'success',
			title: '&nbsp;&nbsp;&nbsp; SUCCESS! {!! session('successMessage') !!} &nbsp'
		})
	});
</script>
@endif
@if(session('infoMessage'))
<script>
	$(function() {
		var Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 3000
		});
		Toast.fire({
			type: 'info',
			title: '&nbsp;&nbsp;&nbsp; INFO! {!! session('infoMessage') !!} &nbsp'
		})
	});
</script>
@endif
@if(session('warningMessage'))
<script>
	$(function() {
		var Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 3000
		});
		Toast.fire({
			type: 'warning',
			title: '&nbsp;&nbsp;&nbsp; WARNING! {!! session('warningMessage') !!} &nbsp'
		})
	});
</script>
@endif
