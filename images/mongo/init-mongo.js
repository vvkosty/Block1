db.createUser(
	{
		user: 'block1',
		pwd: 'block1',
		roles: [
			{
				role: 'readWrite',
				db: 'block1'
			}
		]
	}
);
