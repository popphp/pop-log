CREATE SEQUENCE [{table}]_id_seq START 1;

CREATE TABLE "[{table}]" (
  "id" integer NOT NULL DEFAULT nextval('[{table}]_id_seq'),
  "timestamp" timestamp,
  "priority" integer,
  "name" varchar(255),
  "message" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE [{table}]_id_seq OWNED BY "[{table}]."id";