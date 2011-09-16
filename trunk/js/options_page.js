function show_confirm( action ) {
	var sure = confirm( "Are you sure you want to " + action + "?");
	if ( sure ) {
		return true;
	} else {
		return false;
	}
}