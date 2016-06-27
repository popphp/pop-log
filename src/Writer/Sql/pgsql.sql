CREATE SEQUENCE [{table}]_id_seq START 1;

CREATE TABLE "[{table}]" (
  "id" integer NOT NULL DEFAULT nextval('[{table}]_id_seq'),
  "timestamp" timestamp,
  "level" integer,
  "name" varchar(255),
  "message" text,
  "context" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE [{table}]_id_seq OWNED BY "[{table}]."id";