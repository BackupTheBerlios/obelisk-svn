-- ce script cree un premier utilisateur et inscrit les premiers modules

insert into Context values (0, 'default');

insert into People values (0, 0, NULL, 'admin', true, 'asterisk@localhost');

insert into grp values (0, 'Administrators');

insert into grp_has_people values (0, 0);


