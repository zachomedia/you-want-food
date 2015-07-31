
DROP TRIGGER IF EXISTS update_modified_timestamp ON email_subscriptions;
DROP SEQUENCE IF EXISTS email_subscriptions_seq;
DROP TABLE IF EXISTS email_subscriptions;

CREATE SEQUENCE email_subscriptions_seq;
CREATE TABLE email_subscriptions (
  id bigint PRIMARY KEY DEFAULT nextval('email_subscriptions_seq'),
  email varchar(255) NOT NULL DEFAULT '',
  status int(1) NOT NULL DEFAULT '1',
  signup timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  token varchar(40) NOT NULL DEFAULT '',
  ipaddress varchar(50) NOT NULL DEFAULT ''
);

CREATE TRIGGER update_modified_timestamp BEFORE INSERT OR UPDATE ON email_subscriptions FOR EACH ROW EXECUTE PROCEDURE update_modified_timestamp();

CREATE OR REPLACE FUNCTION update_modified_timestamp() RETURNS TRIGGER LANGUAGE plpgsql AS $$
   BEGIN
   IF (NEW != OLD) THEN
   NEW.modified = CURRENT_TIMESTAMP;
   RETURN NEW;
   END IF;
   RETURN OLD;
   END;
$$;

DROP TABLE IF EXISTS facility_mappings;

CREATE TABLE facility_mappings (
  id bigint NOT NULL,
  facility_id varchar(36) DEFAULT NULL
);

DROP TABLE IF EXISTS inspections_facilities;

CREATE TABLE inspections_facilities (
  id varchar(36) NOT NULL DEFAULT '',
  name varchar(100) DEFAULT NULL,
  telephone varchar(14) DEFAULT NULL,
  street varchar(100) DEFAULT NULL,
  city varchar(50) DEFAULT NULL,
  eatsmart varchar(10) DEFAULT NULL,
  open_date date DEFAULT NULL,
  description text
);

DROP TABLE IF EXISTS inspections_infractions;

CREATE TABLE inspections_infractions (
  id varchar(38) NOT NULL DEFAULT '',
  inspection_id varchar(38) NOT NULL DEFAULT '',
  type varchar(20) DEFAULT NULL,
  category_code text,
  letter_code text,
  description text,
  inspection_date date DEFAULT NULL,
  charge_details text
);


DROP TABLE IF EXISTS inspections_inspections;

CREATE TABLE inspections_inspections (
  id varchar(38) NOT NULL DEFAULT '',
  facility_id varchar(36) NOT NULL DEFAULT '',
  inspection_date date DEFAULT NULL,
  require_reinspection boolean DEFAULT NULL,
  certified_food_handler boolean DEFAULT NULL,
  inspection_type varchar(100) DEFAULT NULL,
  charge_revoked boolean DEFAULT NULL,
  actions text,
  charge_date date DEFAULT NULL
);

DROP TABLE IF EXISTS outlet_reviews;
DROP SEQUENCE IF EXISTS outlet_reviews_seq;

CREATE SEQUENCE outlet_reviews_seq;
CREATE TABLE outlet_reviews (
  id bigint NOT NULL DEFAULT nextval('outlet_reviews_seq'),
  outlet_id bigint NOT NULL,
  reviewer_name varchar(255) DEFAULT NULL,
  reviewer_email varchar(255) DEFAULT NULL,
  review text,
  date timestamp DEFAULT CURRENT_TIMESTAMP,
  ipaddress varchar(50) DEFAULT NULL,
  moderation_status smallint NOT NULL DEFAULT 1,
  moderation_token varchar(40) DEFAULT NULL
);

INSERT INTO facility_mappings (id, facility_id)
VALUES
   (1, '40768627-667E-45B5-AE4A-03107DD759A7'),
   (3, '153CBC51-46CD-462A-BCE2-1FC2C2AA0223'),
   (5, '2592A443-8193-4C29-A3F7-ECC9F0C097DB'),
   (6, '0C908162-36F2-4986-B550-06DA7F5FBD29'),
   (7, '2362785A-6440-40CB-9EBD-07A8E25CEDF1'),
   (20, 'A4C7EE4D-26CD-46F2-A370-0968B02064BF'),
   (21, '03210E94-5A47-468A-B286-B94D1597BBC5'),
   (22, 'B65F837F-C6EF-47E5-900E-351BA480B55C'),
   (23, 'EF62BD1D-186B-4693-A1F6-172BD237122E'),
   (25, 'BE627B66-5D30-43DB-8915-24EEFA7CA954'),
   (123, '1C4151C8-DB3E-43A3-88B6-9734BA1C6C8E'),
   (124, '1D469BEB-227C-48BC-A831-785294B22E00'),
   (128, '52FEB9EB-DADB-4CF0-BB8B-E7512D1A882D'),
   (129, '0C927DEA-0459-46A8-8FA7-3029690641F6'),
   (132, '5B5FE206-11F7-41DB-A27A-EB61342872C2'),
   (137, '98C85A9A-24D5-4C89-863F-B7A165F29BA9'),
   (142, 'C5F256A4-21A5-4AE8-B8F5-F1380BA25B53'),
   (144, 'F596E083-BDDA-4DD9-81B3-1C008DB5D50D'),
   (145, '447E8A7F-569A-4120-BF97-F686BB876BD6'),
   (146, '57C9CB89-9864-496B-84D1-15B25749861A'),
   (147, '08E47040-2420-46DF-A3B7-0B32B5F42554'),
   (303, '3D50D6CD-7394-4B33-868F-63EA03944C12'),
   (374, 'EF8988D0-0E88-4B20-B1BF-876FC33CB7B3'),
   (1210, 'DA838A8F-A20C-4160-BFDC-AAF63D0AC33F'),
   (1211, '62FA8808-D9CE-4505-A390-450D1DF525C4'),
   (1212, 'F3E4D202-FE14-4D42-A68C-7F8071279842'),
   (1213, '1D469BEB-227C-48BC-A831-785294B22E00'),
   (1214, '1D469BEB-227C-48BC-A831-785294B22E00');

