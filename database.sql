create table admin
(
	id int auto_increment comment 'ID beheerder'
		primary key,
	firstname varchar(255) not null comment 'Voornaam beheerder',
	prefix varchar(255) not null comment 'Tussenvoegsel beheerder',
	lastname varchar(255) not null comment 'Achternaam beheerder',
	school_id int null comment 'School Beheerder (Bij NULL: onderzoekend beheerder)',
	password varchar(255) not null comment 'Wachtwoord beheerder'
)
comment 'Beheerders'
;

create table allocations
(
	id int auto_increment
		primary key,
	staff_id int not null,
	assignment_id varchar(36) not null,
	student_id int not null
)
;

create table assignments
(
	id varchar(36) not null comment 'UUID opdracht'
		primary key,
	title varchar(255) not null comment 'Naam opdracht',
	constraint uuid
		unique (id)
)
comment 'Schrijfopdrachten'
;

create table assignments_class
(
	assignment_id varchar(36) null comment 'ID opdracht',
	class_id int null comment 'ID klas',
	start_date datetime null comment 'Startdatum opdracht',
	end_date datetime null comment 'Einddatum opdracht',
	constraint assignments_class_assignment_id_class_id_pk
		unique (assignment_id, class_id)
)
comment 'Koppelt opdrachten aan klassen'
;

create table class
(
	id int auto_increment comment 'ID klas'
		primary key,
	school_id int not null comment 'School klas',
	staff_id int null comment 'Leraar klas',
	level_id int not null comment 'Niveau klas',
	name varchar(255) not null comment 'Naam klas',
	year int(1) not null comment 'Leerjaar klas'
)
comment 'Klassen voor elke school'
;

create table class_staff
(
	class_id int not null,
	staff_id int not null
)
comment 'Toewijzing klas aan staf'
;

create table grading
(
	staff_id int not null comment 'ID beoordelaar',
	submission_id int not null comment 'ID uitwerking',
	type varchar(100) not null comment 'Type deelcijfer',
	grade int not null,
	notes varchar(255) not null,
	primary key (staff_id, submission_id, type)
)
comment 'Becijfering opdrachten'
;

create table level
(
	id int auto_increment comment 'ID niveau'
		primary key,
	name varchar(255) not null comment 'Naam niveau',
	type varchar(255) not null comment 'Type niveau'
)
comment 'Onderwijsniveaus (HAVO/VWO; profielen en richtingen)'
;

create table questioning
(
	student_id int not null,
	questionnaire_id int not null,
	question_id int not null,
	value varchar(255) not null
)
;

create table questionnaire
(
	id int(10) auto_increment
		primary key,
	title varchar(255) not null,
	qualtrics_url varchar(255) not null,
	school_id int not null
)
;

create table questionnaire_question_attributes
(
	id int auto_increment comment 'ID van het attribute'
		primary key,
	questionnairesquestions_id int not null comment 'ID van de questionnairequestion vraag',
	attribute_key varchar(255) not null comment 'Attribute voor de vraag uit de questionnaire',
	attribute_value varchar(255) not null
)
;

create table questionnaire_question_options
(
	id int auto_increment comment 'ID van de option'
		primary key,
	questionnairesquestions_id int not null comment 'ID van de questionnaire question vraag',
	option_key varchar(255) not null comment 'Option voor de vraag uit de questionnaire',
	option_value varchar(255) not null
)
;

create table questionnaires
(
	id int auto_increment comment 'ID van de questionnaire'
		primary key,
	school_id int not null,
	name varchar(255) not null comment 'Naam van de questionnaire',
	action varchar(255) not null,
	method varchar(255) not null
)
;

create table questionnaires_questions
(
	id int auto_increment comment 'ID van de vraag'
		primary key,
	questionnaire_id int not null,
	elementtype varchar(255) not null comment 'Type element van de vraag',
	label varchar(255) not null comment 'Label van de vraag'
)
;

create table reviewerlist
(
	id int(10) auto_increment
		primary key,
	assignment_id varchar(36) not null,
	qualtrics_url varchar(255) not null
)
;

create table reviewerlists
(
	id int auto_increment comment 'ID van de beoordelaarslijst'
		primary key,
	assignment_id varchar(36) not null,
	name varchar(255) not null comment 'Naam van de beoordelaarslijst',
	action varchar(255) not null,
	method varchar(255) not null
)
;

create table reviewerlistsquestions
(
	id int auto_increment comment 'ID van de vraag'
		primary key,
	reviewerlists_id int not null,
	elementtype varchar(255) not null comment 'Type element van de vraag',
	label varchar(255) not null comment 'Label van de vraag'
)
;

create table reviewerlistsquestions_attributes
(
	id int auto_increment comment 'ID van het attribute'
		primary key,
	reviewerlistsquestions_id int not null comment 'ID van de reviewlistquestion vraag',
	attribute_key varchar(255) not null comment 'Attribute voor de vraag uit de beoordelaarslijst',
	attribute_value varchar(255) not null
)
;

create table reviewerlistsquestions_options
(
	id int auto_increment comment 'ID van de option'
		primary key,
	reviewerlistsquestions_id int not null comment 'ID van de reviewlistquestion vraag',
	option_key varchar(255) not null comment 'Option voor de vraag uit de beoordelaarslijst',
	option_value varchar(255) not null
)
;

create table reviewing
(
	staff_id int not null,
	submission_id int not null,
	reviewerlist_id int not null,
	question_id int not null,
	value varchar(255) not null
)
;

create table schools
(
	id int auto_increment comment 'ID school'
		primary key,
	name varchar(255) not null comment 'Naam school',
	type_school int(1) not null comment '0=School; 1=Universiteit'
)
comment 'Schoolgegevens'
;

create table staff
(
	id int auto_increment comment 'ID staflid'
		primary key,
	type int not null comment 'Type beoordelaar (docent=0; extern=1; onderzoek=2)',
	school_id int null comment 'School staflid',
	firstname varchar(255) not null comment 'Voornaam staflid',
	prefix varchar(255) null comment 'Tussenvoegsel staflid',
	lastname varchar(255) not null comment 'Achternaam staflid',
	email varchar(190) null,
	password varchar(255) null comment 'Wachtwoord staflid',
	setuptoken varchar(190) null,
	constraint staff_email_uindex
		unique (email),
	constraint staff_setuptoken_uindex
		unique (setuptoken)
)
;

create trigger before_staff_insert
             before INSERT on staff
             for each row
BEGIN
    SET NEW.setuptoken = uuid();
  END;

create table students
(
	id int not null comment 'Stamnummer leerling'
		primary key,
	school_id int not null comment 'School leerling',
	class_id int not null comment 'Klas leerling',
	firstname varchar(255) not null comment 'Voornaam leerling',
	prefix varchar(255) default '' not null comment 'Tussenvoegsel leerling',
	lastname varchar(255) not null comment 'Achternaam leerling',
	birthday date not null comment 'Geboortedatum leerling',
	password varchar(255) null comment 'Wachtwoord leerling'
)
comment 'Leerlinggegevens'
;

create table submissions
(
	id int auto_increment comment 'ID uitwerking'
		primary key,
	time timestamp default CURRENT_TIMESTAMP not null comment 'Datum inleveren uitwerking',
	student_id int not null comment 'Leerling uitwerking',
	assignment_id varchar(36) not null comment 'Opdracht uitwerking',
	file varchar(255) not null comment 'Bestandsnaam uitwerking',
	original_file varchar(255) null,
	text longtext not null comment 'Ruwe tekst uitwerking',
	submission_count int default '1' null
)
comment 'Uitwerkingen leerlingen'
;

create table submissions_staff
(
	submission_id int not null,
	staff_id int not null
)
comment 'Toewijzing uitwerkingen aan staflid'
;

create view grade_total as 
SELECT
    `hofstad`.`grading`.`staff_id`             AS `staff_id`,
    `hofstad`.`grading`.`submission_id`        AS `submission_id`,
    round(avg(`hofstad`.`grading`.`grade`), 2) AS `final_grade`
  FROM `hofstad`.`grading`
  WHERE (`hofstad`.`grading`.`grade` <> 0)
  GROUP BY `hofstad`.`grading`.`staff_id`, `hofstad`.`grading`.`submission_id`;

create function wordcount (str text) returns int 
;

