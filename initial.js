var initialUser = {
	name: 'Admin Kadoo',
	email: 'admin@kadoo.mx',
	password: '7110eda4d09e062aa5e4a390b0a572ac0d2c0220',
	token: '1234',
	roles: ['superadmin'],
	active: true,
	deleted: false,
};
db.users.insert(initialUser);