-- This is an initilisation script for your databse. It's the good time to 
-- think about your dialplan and write it down

-- modules
insert into modules values ( 1, 'people');
insert into modules values ( 2, 'geo');
insert into modules values ( 3, 'outside);
insert into modules values ( 4, 'menu');

-- number between 100 and 999 are geographical extension
insert into extension values ( 100, NULL, 999, 2); 

-- number between 1000 and 9999 are people
insert into extension values ( 1000, NULL, 9999, 1);

-- number between 10000 and 9223372036854775807 are outside
insert into extension values (10000, NULL, 9223372036854775807, 3);

-- one administrator (set your password here an integer value)
insert into People values (0, 'admin', 'admin', 'admin', '1234', true, 
				'asterisk@localhost');
insert into grp values (0, 'Administrators');
insert into grp_has_people values (0, 0);



