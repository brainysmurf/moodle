-- Execute all the statements in this file to reate the ssis metadata tables
-- You may need to change the 'OWNER TO' lines to use the correct database username


-- Table: ssismdl_course_ssis_metadata

-- DROP TABLE ssismdl_course_ssis_metadata;

CREATE TABLE ssismdl_course_ssis_metadata
(
  courseid integer NOT NULL,
  field character varying(255) NOT NULL,
  value character varying(255),
  CONSTRAINT ssismdl_course_metadata_pk PRIMARY KEY (courseid, field)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE ssismdl_course_ssis_metadata
  OWNER TO moodle;
COMMENT ON TABLE ssismdl_course_ssis_metadata
  IS 'Stores custom field data for courses.';


-- Table: ssismdl_category_ssis_metadata

-- DROP TABLE ssismdl_category_ssis_metadata;

CREATE TABLE ssismdl_category_ssis_metadata
(
  categoryid integer NOT NULL,
  field character varying(255) NOT NULL,
  value character varying(255),
  CONSTRAINT ssismdl_category_metadata_pk PRIMARY KEY (categoryid, field)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE ssismdl_category_ssis_metadata
  OWNER TO moodle;
COMMENT ON TABLE ssismdl_category_ssis_metadata
  IS 'Stores custom field data for categorys.';
