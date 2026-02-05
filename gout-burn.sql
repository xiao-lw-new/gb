--
-- PostgreSQL database dump
--

\restrict 4uLWlKuoSoKFCwaih82VBGuW0WxKIDPV72gNqgEppBLNCBtQoYAKuIwyuocXIk2

-- Dumped from database version 15.13
-- Dumped by pg_dump version 17.7 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: timescaledb; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS timescaledb WITH SCHEMA public;


--
-- Name: EXTENSION timescaledb; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION timescaledb IS 'Enables scalable inserts and complex queries for time-series data (Community Edition)';


--
-- Name: citext; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: address_sign_code; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.address_sign_code (
    id bigint NOT NULL,
    address character varying(50) NOT NULL,
    type smallint DEFAULT '0'::smallint NOT NULL,
    code character varying(50) NOT NULL,
    expired smallint DEFAULT '0'::smallint NOT NULL,
    retry smallint DEFAULT '0'::smallint NOT NULL,
    expired_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.address_sign_code OWNER TO postgres;

--
-- Name: address_sign_code_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.address_sign_code_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.address_sign_code_id_seq OWNER TO postgres;

--
-- Name: address_sign_code_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.address_sign_code_id_seq OWNED BY public.address_sign_code.id;


--
-- Name: admin_users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admin_users (
    id bigint NOT NULL,
    username character varying(255) NOT NULL,
    name character varying(255),
    email character varying(255),
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    modules json,
    is_super boolean DEFAULT false NOT NULL
);


ALTER TABLE public.admin_users OWNER TO postgres;

--
-- Name: admin_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.admin_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.admin_users_id_seq OWNER TO postgres;

--
-- Name: admin_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admin_users_id_seq OWNED BY public.admin_users.id;


--
-- Name: blockchain_block; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_block (
    id bigint NOT NULL,
    chain character varying(50) NOT NULL,
    last_block bigint DEFAULT '0'::bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blockchain_block OWNER TO postgres;

--
-- Name: blockchain_block_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_block_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_block_id_seq OWNER TO postgres;

--
-- Name: blockchain_block_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_block_id_seq OWNED BY public.blockchain_block.id;


--
-- Name: blockchain_check_newblock; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_check_newblock (
    id bigint NOT NULL,
    "currentBlock" bigint NOT NULL,
    "lastBlock" bigint NOT NULL,
    started_at timestamp(0) without time zone,
    ended_at timestamp(0) without time zone,
    times integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blockchain_check_newblock OWNER TO postgres;

--
-- Name: blockchain_check_newblock_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_check_newblock_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_check_newblock_id_seq OWNER TO postgres;

--
-- Name: blockchain_check_newblock_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_check_newblock_id_seq OWNED BY public.blockchain_check_newblock.id;


--
-- Name: blockchain_contract; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_contract (
    id bigint NOT NULL,
    name character varying(100),
    chain_id integer NOT NULL,
    address character varying(100) NOT NULL,
    abi_path character varying(255) NOT NULL,
    remark character varying(255),
    status smallint DEFAULT '1'::smallint NOT NULL,
    default_account character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blockchain_contract OWNER TO postgres;

--
-- Name: blockchain_contract_event; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_contract_event (
    id bigint NOT NULL,
    event_name character varying(100) NOT NULL,
    handler character varying(255) NOT NULL,
    topic character varying(255),
    contract_id integer NOT NULL,
    remark character varying(255),
    status smallint DEFAULT '1'::smallint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blockchain_contract_event OWNER TO postgres;

--
-- Name: blockchain_contract_event_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_contract_event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_contract_event_id_seq OWNER TO postgres;

--
-- Name: blockchain_contract_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_contract_event_id_seq OWNED BY public.blockchain_contract_event.id;


--
-- Name: blockchain_contract_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_contract_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_contract_id_seq OWNER TO postgres;

--
-- Name: blockchain_contract_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_contract_id_seq OWNED BY public.blockchain_contract.id;


--
-- Name: blockchain_contract_sender_wallets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_contract_sender_wallets (
    id bigint NOT NULL,
    wallet_name character varying(100),
    address character varying(100),
    encrypted_private_key text,
    status smallint DEFAULT '1'::smallint NOT NULL,
    remark character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_default smallint
);


ALTER TABLE public.blockchain_contract_sender_wallets OWNER TO postgres;

--
-- Name: blockchain_contract_sender_wallets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_contract_sender_wallets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_contract_sender_wallets_id_seq OWNER TO postgres;

--
-- Name: blockchain_contract_sender_wallets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_contract_sender_wallets_id_seq OWNED BY public.blockchain_contract_sender_wallets.id;


--
-- Name: blockchain_event_error; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_event_error (
    id bigint NOT NULL,
    contract_name character varying(100) NOT NULL,
    event_name character varying(100) NOT NULL,
    transaction_hash character varying(100) NOT NULL,
    log_index character varying(50) NOT NULL,
    event_data json NOT NULL,
    handler_class character varying(255) NOT NULL,
    error_message text NOT NULL,
    error_trace text,
    status smallint DEFAULT '0'::smallint NOT NULL,
    retry_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blockchain_event_error OWNER TO postgres;

--
-- Name: COLUMN blockchain_event_error.status; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.blockchain_event_error.status IS '0:待处理, 1:已修复, 2:已忽略';


--
-- Name: blockchain_event_error_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_event_error_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_event_error_id_seq OWNER TO postgres;

--
-- Name: blockchain_event_error_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_event_error_id_seq OWNED BY public.blockchain_event_error.id;


--
-- Name: blockchain_events; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_events (
    id bigint NOT NULL,
    transaction_hash character varying(255),
    block_number integer,
    block_time bigint,
    event_name character varying(255),
    event_data json,
    contract_name character varying(255),
    log_index character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blockchain_events OWNER TO postgres;

--
-- Name: blockchain_events_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_events_id_seq OWNER TO postgres;

--
-- Name: blockchain_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_events_id_seq OWNED BY public.blockchain_events.id;


--
-- Name: blockchain_rpc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_rpc (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    provider character varying(100) NOT NULL,
    chain_id character varying(25) NOT NULL,
    gas_limit character varying(100),
    gas_price character varying(100),
    status smallint DEFAULT '1'::smallint,
    response_time integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    explorer_url character varying(255)
);


ALTER TABLE public.blockchain_rpc OWNER TO postgres;

--
-- Name: COLUMN blockchain_rpc.explorer_url; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.blockchain_rpc.explorer_url IS '区块浏览器地址';


--
-- Name: blockchain_rpc_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_rpc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_rpc_id_seq OWNER TO postgres;

--
-- Name: blockchain_rpc_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_rpc_id_seq OWNED BY public.blockchain_rpc.id;


--
-- Name: blockchain_transaction_queue; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blockchain_transaction_queue (
    id bigint NOT NULL,
    transaction_hash character varying(100) NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    block_number bigint,
    block_time bigint,
    event_data json,
    message text,
    retry_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    user_id bigint,
    address character varying(100)
);


ALTER TABLE public.blockchain_transaction_queue OWNER TO postgres;

--
-- Name: blockchain_transaction_queue_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blockchain_transaction_queue_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.blockchain_transaction_queue_id_seq OWNER TO postgres;

--
-- Name: blockchain_transaction_queue_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blockchain_transaction_queue_id_seq OWNED BY public.blockchain_transaction_queue.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: system_setting; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.system_setting (
    id bigint NOT NULL,
    key character varying(100) NOT NULL,
    value text NOT NULL,
    type character varying(20) DEFAULT 'string'::character varying NOT NULL,
    description character varying(255),
    category character varying(50) DEFAULT 'general'::character varying NOT NULL,
    is_editable boolean DEFAULT true NOT NULL,
    is_public boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.system_setting OWNER TO postgres;

--
-- Name: system_setting_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.system_setting_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.system_setting_id_seq OWNER TO postgres;

--
-- Name: system_setting_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.system_setting_id_seq OWNED BY public.system_setting.id;


--
-- Name: tax_processor_dispatch_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tax_processor_dispatch_logs (
    id bigint NOT NULL,
    chain_id character varying(255) NOT NULL,
    transaction_hash character varying(100) NOT NULL,
    log_index integer NOT NULL,
    block_number bigint,
    block_time timestamp(0) without time zone,
    contract_address character varying(100),
    tax_token character varying(100),
    fee_amount_wei character varying(80) DEFAULT '0'::character varying NOT NULL,
    market_amount_wei character varying(80) DEFAULT '0'::character varying NOT NULL,
    dividend_amount_wei character varying(80) DEFAULT '0'::character varying NOT NULL,
    fee_amount character varying(80) DEFAULT '0'::character varying NOT NULL,
    market_amount character varying(80) DEFAULT '0'::character varying NOT NULL,
    dividend_amount character varying(80) DEFAULT '0'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tax_processor_dispatch_logs OWNER TO postgres;

--
-- Name: tax_processor_dispatch_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tax_processor_dispatch_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tax_processor_dispatch_logs_id_seq OWNER TO postgres;

--
-- Name: tax_processor_dispatch_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tax_processor_dispatch_logs_id_seq OWNED BY public.tax_processor_dispatch_logs.id;


--
-- Name: telegram_bot; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telegram_bot (
    id bigint NOT NULL,
    bot_name character varying(100) NOT NULL,
    bot_token character varying(255) NOT NULL,
    remark character varying(255),
    status smallint DEFAULT '1'::smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.telegram_bot OWNER TO postgres;

--
-- Name: telegram_bot_group; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telegram_bot_group (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    chat_id character varying(100) NOT NULL,
    channel character varying(100) NOT NULL,
    remark character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.telegram_bot_group OWNER TO postgres;

--
-- Name: telegram_bot_group_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telegram_bot_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telegram_bot_group_id_seq OWNER TO postgres;

--
-- Name: telegram_bot_group_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telegram_bot_group_id_seq OWNED BY public.telegram_bot_group.id;


--
-- Name: telegram_bot_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telegram_bot_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telegram_bot_id_seq OWNER TO postgres;

--
-- Name: telegram_bot_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telegram_bot_id_seq OWNED BY public.telegram_bot.id;


--
-- Name: telegram_contribution_group; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telegram_contribution_group (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    chat_id character varying(100) NOT NULL,
    channel character varying(100) NOT NULL,
    remark character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.telegram_contribution_group OWNER TO postgres;

--
-- Name: telegram_contribution_group_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telegram_contribution_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telegram_contribution_group_id_seq OWNER TO postgres;

--
-- Name: telegram_contribution_group_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telegram_contribution_group_id_seq OWNED BY public.telegram_contribution_group.id;


--
-- Name: telegram_group_messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telegram_group_messages (
    id bigint NOT NULL,
    message_type integer DEFAULT 4 NOT NULL,
    title character varying(255),
    content text,
    sender character varying(100),
    sender_name character varying(100),
    priority integer DEFAULT 0 NOT NULL,
    business_id integer,
    status smallint DEFAULT '0'::smallint NOT NULL,
    address_id bigint,
    address character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    retry_count integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.telegram_group_messages OWNER TO postgres;

--
-- Name: telegram_group_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telegram_group_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telegram_group_messages_id_seq OWNER TO postgres;

--
-- Name: telegram_group_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telegram_group_messages_id_seq OWNED BY public.telegram_group_messages.id;


--
-- Name: telegram_messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telegram_messages (
    id bigint NOT NULL,
    message_type integer DEFAULT 4 NOT NULL,
    title character varying(255),
    content text,
    sender character varying(100),
    sender_name character varying(100),
    priority integer DEFAULT 0 NOT NULL,
    business_id integer,
    status smallint DEFAULT '0'::smallint NOT NULL,
    address_id bigint,
    address character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    retry_count integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.telegram_messages OWNER TO postgres;

--
-- Name: COLUMN telegram_messages.message_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.telegram_messages.message_type IS '4: Telegram';


--
-- Name: COLUMN telegram_messages.status; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.telegram_messages.status IS '0: Pending, 1: Processing, 2: Success, 3: Failed';


--
-- Name: telegram_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telegram_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telegram_messages_id_seq OWNER TO postgres;

--
-- Name: telegram_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telegram_messages_id_seq OWNED BY public.telegram_messages.id;


--
-- Name: token_price; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.token_price (
    id bigint NOT NULL,
    token_name character varying(32) NOT NULL,
    token_price numeric(36,18) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.token_price OWNER TO postgres;

--
-- Name: token_price_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.token_price_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.token_price_id_seq OWNER TO postgres;

--
-- Name: token_price_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.token_price_id_seq OWNED BY public.token_price.id;


--
-- Name: token_price_up_chain; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.token_price_up_chain (
    id bigint NOT NULL,
    token_name character varying(32) NOT NULL,
    token_price numeric(36,18) NOT NULL,
    transaction_hash character varying(100) NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    token_price_chain numeric(78,0)
);


ALTER TABLE public.token_price_up_chain OWNER TO postgres;

--
-- Name: token_price_up_chain_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.token_price_up_chain_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.token_price_up_chain_id_seq OWNER TO postgres;

--
-- Name: token_price_up_chain_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.token_price_up_chain_id_seq OWNED BY public.token_price_up_chain.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    email_verified_at timestamp(0) without time zone,
    password character varying(255),
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    address public.citext,
    p_id bigint DEFAULT '0'::bigint NOT NULL,
    path text,
    remark character varying(255),
    status smallint DEFAULT '0'::smallint NOT NULL,
    active smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: COLUMN users.status; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.users.status IS '0:预创建, 1:正常, 2:禁用';


--
-- Name: COLUMN users.active; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.users.active IS '激活状态: 0未激活, 1已激活';


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: address_sign_code id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.address_sign_code ALTER COLUMN id SET DEFAULT nextval('public.address_sign_code_id_seq'::regclass);


--
-- Name: admin_users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users ALTER COLUMN id SET DEFAULT nextval('public.admin_users_id_seq'::regclass);


--
-- Name: blockchain_block id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_block ALTER COLUMN id SET DEFAULT nextval('public.blockchain_block_id_seq'::regclass);


--
-- Name: blockchain_check_newblock id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_check_newblock ALTER COLUMN id SET DEFAULT nextval('public.blockchain_check_newblock_id_seq'::regclass);


--
-- Name: blockchain_contract id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_contract ALTER COLUMN id SET DEFAULT nextval('public.blockchain_contract_id_seq'::regclass);


--
-- Name: blockchain_contract_event id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_contract_event ALTER COLUMN id SET DEFAULT nextval('public.blockchain_contract_event_id_seq'::regclass);


--
-- Name: blockchain_contract_sender_wallets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_contract_sender_wallets ALTER COLUMN id SET DEFAULT nextval('public.blockchain_contract_sender_wallets_id_seq'::regclass);


--
-- Name: blockchain_event_error id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_event_error ALTER COLUMN id SET DEFAULT nextval('public.blockchain_event_error_id_seq'::regclass);


--
-- Name: blockchain_events id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_events ALTER COLUMN id SET DEFAULT nextval('public.blockchain_events_id_seq'::regclass);


--
-- Name: blockchain_rpc id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_rpc ALTER COLUMN id SET DEFAULT nextval('public.blockchain_rpc_id_seq'::regclass);


--
-- Name: blockchain_transaction_queue id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_transaction_queue ALTER COLUMN id SET DEFAULT nextval('public.blockchain_transaction_queue_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: system_setting id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_setting ALTER COLUMN id SET DEFAULT nextval('public.system_setting_id_seq'::regclass);


--
-- Name: tax_processor_dispatch_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_processor_dispatch_logs ALTER COLUMN id SET DEFAULT nextval('public.tax_processor_dispatch_logs_id_seq'::regclass);


--
-- Name: telegram_bot id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_bot ALTER COLUMN id SET DEFAULT nextval('public.telegram_bot_id_seq'::regclass);


--
-- Name: telegram_bot_group id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_bot_group ALTER COLUMN id SET DEFAULT nextval('public.telegram_bot_group_id_seq'::regclass);


--
-- Name: telegram_contribution_group id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_contribution_group ALTER COLUMN id SET DEFAULT nextval('public.telegram_contribution_group_id_seq'::regclass);


--
-- Name: telegram_group_messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_group_messages ALTER COLUMN id SET DEFAULT nextval('public.telegram_group_messages_id_seq'::regclass);


--
-- Name: telegram_messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_messages ALTER COLUMN id SET DEFAULT nextval('public.telegram_messages_id_seq'::regclass);


--
-- Name: token_price id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.token_price ALTER COLUMN id SET DEFAULT nextval('public.token_price_id_seq'::regclass);


--
-- Name: token_price_up_chain id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.token_price_up_chain ALTER COLUMN id SET DEFAULT nextval('public.token_price_up_chain_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: hypertable; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.hypertable (id, schema_name, table_name, associated_schema_name, associated_table_prefix, num_dimensions, chunk_sizing_func_schema, chunk_sizing_func_name, chunk_target_size, compression_state, compressed_hypertable_id, status) FROM stdin;
\.


--
-- Data for Name: chunk; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.chunk (id, hypertable_id, schema_name, table_name, compressed_chunk_id, dropped, status, osm_chunk, creation_time) FROM stdin;
\.


--
-- Data for Name: chunk_column_stats; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.chunk_column_stats (id, hypertable_id, chunk_id, column_name, range_start, range_end, valid) FROM stdin;
\.


--
-- Data for Name: dimension; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.dimension (id, hypertable_id, column_name, column_type, aligned, num_slices, partitioning_func_schema, partitioning_func, interval_length, compress_interval_length, integer_now_func_schema, integer_now_func) FROM stdin;
\.


--
-- Data for Name: dimension_slice; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.dimension_slice (id, dimension_id, range_start, range_end) FROM stdin;
\.


--
-- Data for Name: chunk_constraint; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.chunk_constraint (chunk_id, dimension_slice_id, constraint_name, hypertable_constraint_name) FROM stdin;
\.


--
-- Data for Name: compression_chunk_size; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.compression_chunk_size (chunk_id, compressed_chunk_id, uncompressed_heap_size, uncompressed_toast_size, uncompressed_index_size, compressed_heap_size, compressed_toast_size, compressed_index_size, numrows_pre_compression, numrows_post_compression, numrows_frozen_immediately) FROM stdin;
\.


--
-- Data for Name: compression_settings; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.compression_settings (relid, compress_relid, segmentby, orderby, orderby_desc, orderby_nullsfirst, index) FROM stdin;
\.


--
-- Data for Name: continuous_agg; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_agg (mat_hypertable_id, raw_hypertable_id, parent_mat_hypertable_id, user_view_schema, user_view_name, partial_view_schema, partial_view_name, direct_view_schema, direct_view_name, materialized_only, finalized) FROM stdin;
\.


--
-- Data for Name: continuous_agg_migrate_plan; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_agg_migrate_plan (mat_hypertable_id, start_ts, end_ts, user_view_definition) FROM stdin;
\.


--
-- Data for Name: continuous_agg_migrate_plan_step; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_agg_migrate_plan_step (mat_hypertable_id, step_id, status, start_ts, end_ts, type, config) FROM stdin;
\.


--
-- Data for Name: continuous_aggs_bucket_function; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_aggs_bucket_function (mat_hypertable_id, bucket_func, bucket_width, bucket_origin, bucket_offset, bucket_timezone, bucket_fixed_width) FROM stdin;
\.


--
-- Data for Name: continuous_aggs_hypertable_invalidation_log; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_aggs_hypertable_invalidation_log (hypertable_id, lowest_modified_value, greatest_modified_value) FROM stdin;
\.


--
-- Data for Name: continuous_aggs_invalidation_threshold; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_aggs_invalidation_threshold (hypertable_id, watermark) FROM stdin;
\.


--
-- Data for Name: continuous_aggs_materialization_invalidation_log; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_aggs_materialization_invalidation_log (materialization_id, lowest_modified_value, greatest_modified_value) FROM stdin;
\.


--
-- Data for Name: continuous_aggs_materialization_ranges; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_aggs_materialization_ranges (materialization_id, lowest_modified_value, greatest_modified_value) FROM stdin;
\.


--
-- Data for Name: continuous_aggs_watermark; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.continuous_aggs_watermark (mat_hypertable_id, watermark) FROM stdin;
\.


--
-- Data for Name: metadata; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.metadata (key, value, include_in_telemetry) FROM stdin;
install_timestamp	2025-11-27 03:55:30.166871+00	t
timescaledb_version	2.23.1	f
exported_uuid	ee90aba5-5286-4998-ac50-b796a45eb71b	t
\.


--
-- Data for Name: tablespace; Type: TABLE DATA; Schema: _timescaledb_catalog; Owner: postgres
--

COPY _timescaledb_catalog.tablespace (id, hypertable_id, tablespace_name) FROM stdin;
\.


--
-- Data for Name: bgw_job; Type: TABLE DATA; Schema: _timescaledb_config; Owner: postgres
--

COPY _timescaledb_config.bgw_job (id, application_name, schedule_interval, max_runtime, max_retries, retry_period, proc_schema, proc_name, owner, scheduled, fixed_schedule, initial_start, hypertable_id, config, check_schema, check_name, timezone) FROM stdin;
\.


--
-- Data for Name: address_sign_code; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.address_sign_code (id, address, type, code, expired, retry, expired_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: admin_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admin_users (id, username, name, email, password, remember_token, created_at, updated_at, modules, is_super) FROM stdin;
\.


--
-- Data for Name: blockchain_block; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_block (id, chain, last_block, created_at, updated_at) FROM stdin;
1	56:12	790903870	2026-02-05 10:06:07	2026-02-05 10:06:07
2	56:62	790903870	2026-02-05 10:06:07	2026-02-05 10:06:07
3	56:112	790903870	2026-02-05 10:06:07	2026-02-05 10:06:07
\.


--
-- Data for Name: blockchain_check_newblock; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_check_newblock (id, "currentBlock", "lastBlock", started_at, ended_at, times, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: blockchain_contract; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_contract (id, name, chain_id, address, abi_path, remark, status, default_account, created_at, updated_at) FROM stdin;
1	ReleasePool	56	0x75efa4ceadc7608972ecc810b0587887048f3d72	build/ReleasePool.json	ReleasePool contract	1	\N	2026-02-05 14:56:35	2026-02-05 14:56:35
2	TaxProcessor	56	0x952e12e59be5363dbb16696a8bdd62e6589659e3	build/TaxProcessor.json	TaxProcessor contract	1	\N	2026-02-05 09:47:36	2026-02-05 09:47:36
\.


--
-- Data for Name: blockchain_contract_event; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_contract_event (id, event_name, handler, topic, contract_id, remark, status, created_at, updated_at) FROM stdin;
1	EventStartRelease	App\\Modules\\Mg\\Handler\\EventStartReleaseHandler	0x4afd3bad2ad1426c70bc0caab1a8037c85bbf6a2ac5898f090bc8b4927e13d50	1	EventStartRelease(address,uint256,uint256,uint256,uint256,uint256)	1	2026-02-05 14:56:35	2026-02-05 14:56:35
2	EventClaimRelease	App\\Modules\\Mg\\Handler\\EventClaimReleaseHandler	0xf1d41aa224bbf98ab2a22505bf399d4a4e2a0fa02f26a82125df08a7f232574b	1	EventClaimRelease(address,uint256,uint256,(uint256,uint256,uint256,uint256,uint256,uint256,uint256,uint256,uint256))	1	2026-02-05 14:56:35	2026-02-05 14:56:35
3	EventClaimReleaseAll	App\\Modules\\Mg\\Handler\\EventClaimReleaseAllHandler	0xbc6c628c3503d1583b5340156913137980a9ec61969aefe2d05590a17088bb13	1	EventClaimReleaseAll(address,uint256,uint256,(uint256,uint256,uint256,uint256,uint256,uint256,uint256,uint256,uint256)[])	1	2026-02-05 14:56:35	2026-02-05 14:56:35
4	FlapTaxProcessorDispatchExecuted	App\\Modules\\Blockchain\\Handlers\\FlapTaxProcessorDispatchExecutedHandler	0x172485312163eefa9f05b438339dc7c596fbb24af0cb3e35b9130c68453a0d88	2	FlapTaxProcessorDispatchExecuted(address,uint256,uint256,uint256)	1	2026-02-05 09:55:01	2026-02-05 09:55:01
\.


--
-- Data for Name: blockchain_contract_sender_wallets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_contract_sender_wallets (id, wallet_name, address, encrypted_private_key, status, remark, created_at, updated_at, is_default) FROM stdin;
\.


--
-- Data for Name: blockchain_event_error; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_event_error (id, contract_name, event_name, transaction_hash, log_index, event_data, handler_class, error_message, error_trace, status, retry_count, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: blockchain_events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_events (id, transaction_hash, block_number, block_time, event_name, event_data, contract_name, log_index, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: blockchain_rpc; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_rpc (id, name, provider, chain_id, gas_limit, gas_price, status, response_time, created_at, updated_at, explorer_url) FROM stdin;
1	bsc_main_1	http://54.250.147.179:8545	56	500000	0.05	1	\N	2026-02-05 17:44:28	2026-02-05 17:44:25	\N
\.


--
-- Data for Name: blockchain_transaction_queue; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blockchain_transaction_queue (id, transaction_hash, status, block_number, block_time, event_data, message, retry_count, created_at, updated_at, user_id, address) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-system_warning:exception:57d81bfc920d1708a669d681f4185d372036b414	i:1;	1770273846
laravel-cache-system_warning:exception:4f5ecd5da790b4756aa416e4e46d6fd89d1a42bb	i:1;	1770273889
laravel-cache-system_warning:exception:df6737e37b1d085be545aba7408a5cf566470514	i:1;	1770274503
laravel-cache-system_warning:exception:6e12e60d71330138af96863d0f05929b95ffcb8e	i:1;	1770274643
laravel-cache-system_warning:exception:2f5dda62aa0365be30a7d05954bf245833216b18	i:1;	1770274661
laravel-cache-telegram:trigger_process:cooldown	i:1;	1770274371
laravel-cache-system_setting:chain_id	s:2:"56";	1770288984
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2026_01_09_024839_create_address_sign_code_table	1
2	2026_01_09_024839_create_blockchain_block_table	1
3	2026_01_09_024839_create_blockchain_check_newblock_table	1
4	2026_01_09_024839_create_blockchain_contract_event_table	1
5	2026_01_09_024839_create_blockchain_contract_sender_wallets_table	1
6	2026_01_09_024839_create_blockchain_contract_table	1
7	2026_01_09_024839_create_blockchain_event_error_table	1
8	2026_01_09_024839_create_blockchain_events_table	1
9	2026_01_09_024839_create_blockchain_rpc_table	1
10	2026_01_09_024839_create_blockchain_transaction_queue_table	1
11	2026_01_09_024839_create_cache_locks_table	1
12	2026_01_09_024839_create_cache_table	1
13	2026_01_09_024839_create_failed_jobs_table	1
14	2026_01_09_024839_create_job_batches_table	1
15	2026_01_09_024839_create_jobs_table	1
159	2026_02_05_000001_create_tax_processor_dispatch_logs_table	5
160	2026_02_05_000002_sync_taxprocessor_event_flap_dispatch	5
34	2026_01_09_024839_create_password_reset_tokens_table	1
35	2026_01_09_024839_create_sessions_table	1
36	2026_01_09_024839_create_telegram_bot_group_table	1
37	2026_01_09_024839_create_telegram_bot_table	1
38	2026_01_09_024839_create_telegram_contribution_group_table	1
39	2026_01_09_024839_create_telegram_group_messages_table	1
40	2026_01_09_024839_create_telegram_messages_table	1
42	2026_01_09_025019_create_users_table	2
43	2026_01_09_025021_create_add_columnstore_policy_proc	3
44	2026_01_09_025021_create_add_process_hypertable_invalidations_policy_proc	4
45	2026_01_09_025021_create_attach_chunk_proc	4
46	2026_01_09_025021_create_cagg_migrate_proc	4
47	2026_01_09_025021_create_convert_to_columnstore_proc	4
48	2026_01_09_025021_create_convert_to_rowstore_proc	4
49	2026_01_09_025021_create_detach_chunk_proc	4
50	2026_01_09_025021_create_merge_chunks_proc	4
51	2026_01_09_025021_create_recompress_chunk_proc	4
52	2026_01_09_025021_create_refresh_continuous_aggregate_proc	4
53	2026_01_09_025021_create_remove_columnstore_policy_proc	4
54	2026_01_09_025021_create_remove_process_hypertable_invalidations_policy_proc	4
55	2026_01_09_025021_create_run_job_proc	4
56	2026_01_09_025021_create_split_chunk_proc	4
57	2026_01_09_064417_create_system_setting_table	4
58	2026_01_09_065106_create_personal_access_tokens_table	4
59	2026_01_09_095551_make_email_and_password_nullable_in_users_table	4
72	2026_01_11_070126_create_admin_users_table	4
73	2026_01_11_122423_make_email_nullable_in_users_table	4
74	2026_01_11_123037_make_password_nullable_in_users_table	4
77	2026_01_13_072513_change_users_address_to_citext	4
80	2026_01_13_204519_update_blockchain_contract_sender_wallets_structure	4
89	2026_01_16_000002_create_token_price_table	4
90	2026_01_16_000003_create_token_price_up_chain_table	4
92	2026_01_16_160500_add_token_price_chain_to_token_price_up_chain_table	4
93	2026_01_17_120000_add_new_fields_for_minepool_events	4
94	2026_01_17_120100_sync_minepool_contract_events_with_abi	4
95	2026_01_27_180000_add_modules_to_admin_users_table	4
96	2026_01_27_190000_add_is_super_to_admin_users_table	4
100	2026_01_30_000003_sync_releasepool_event_start_release	4
102	2026_01_30_000005_sync_releasepool_event_claim_release	4
103	2026_01_30_000006_fix_releasepool_event_topics	4
105	2026_01_30_000008_sync_releasepool_event_claim_release_all	4
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: system_setting; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.system_setting (id, key, value, type, description, category, is_editable, is_public, created_at, updated_at) FROM stdin;
1	chain_id	56	string	\N	general	t	f	2026-02-05 10:09:07	2026-02-05 10:09:07
\.


--
-- Data for Name: tax_processor_dispatch_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tax_processor_dispatch_logs (id, chain_id, transaction_hash, log_index, block_number, block_time, contract_address, tax_token, fee_amount_wei, market_amount_wei, dividend_amount_wei, fee_amount, market_amount, dividend_amount, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: telegram_bot; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telegram_bot (id, bot_name, bot_token, remark, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: telegram_bot_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telegram_bot_group (id, name, chat_id, channel, remark, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: telegram_contribution_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telegram_contribution_group (id, name, chat_id, channel, remark, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: telegram_group_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telegram_group_messages (id, message_type, title, content, sender, sender_name, priority, business_id, status, address_id, address, created_at, updated_at, retry_count) FROM stdin;
\.


--
-- Data for Name: telegram_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telegram_messages (id, message_type, title, content, sender, sender_name, priority, business_id, status, address_id, address, created_at, updated_at, retry_count) FROM stdin;
1	4	🚨 System Warning	🧩 Type: Illuminate\\Database\\QueryException\n💥 Message: SQLSTATE[42704]: Undefined object: 7 ERROR:  operator class "gin_trgm_ops" does not exist for access method "gin" (Connection: pgsql, Host: 127.0.0.1, Port: 5433, Database: gout-burn, SQL: CREATE INDEX idx_users_path_gin_trgm ON users USING gin (path gin_trgm_ops))\n📍 Location: /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php:831\n🌍 Env: local\n🧵 Trace (top 20):\n#0 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php(787): Illuminate\\Database\\Connection->runQueryCallback('CREATE INDEX id...', Array, Object(Closure))\n#1 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php(566): Illuminate\\Database\\Connection->run('CREATE INDEX id...', Array, Object(Closure))\n#2 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php(491): Illuminate\\Database\\Connection->statement('CREATE INDEX id...')\n#3 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Support/Facades/Facade.php(363): Illuminate\\Database\\DatabaseManager->__call('statement', Array)\n#4 /Users/opts/projects/gout-burn/gb/database/migrations/2026_01_09_025019_create_users_table.php(32): Illuminate\\Support\\Facades\\Facade::__callStatic('statement', Array)\n#5 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(517): Illuminate\\Database\\Migrations\\Migration@anonymous->up()\n#6 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(442): Illuminate\\Database\\Migrations\\Migrator->runMethod(Object(Illuminate\\Database\\PostgresConnection), Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#7 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Concerns/ManagesTransactions.php(35): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}(Object(Illuminate\\Database\\PostgresConnection))\n#8 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(450): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#9 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->runMigration(Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#10 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Console/View/Components/Task.php(41): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}()\n#11 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(812): Illuminate\\Console\\View\\Components\\Task->render('2026_01_09_0250...', Object(Closure))\n#12 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->write('Illuminate\\\\Cons...', '2026_01_09_0250...', Object(Closure))\n#13 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(211): Illuminate\\Database\\Migrations\\Migrator->runUp('/Users/opts/pro...', 1, false)\n#14 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(138): Illuminate\\Database\\Migrations\\Migrator->runPending(Array, Array)\n#15 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(116): Illuminate\\Database\\Migrations\\Migrator->run(Array, Array)\n#16 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(669): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->Illuminate\\Database\\Console\\Migrations\\{closure}()\n#17 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(109): Illuminate\\Database\\Migrations\\Migrator->usingConnection(NULL, Object(Closure))\n#18 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(88): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->runMigrations()\n#19 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->handle()	system_warning	system	0	\N	0	\N	\N	2026-02-05 14:39:06	2026-02-05 14:39:06	0
2	4	🚨 System Warning	🧩 Type: Illuminate\\Database\\QueryException\n💥 Message: SQLSTATE[58P01]: Undefined file: 7 ERROR:  could not access file "$libdir/timescaledb-2.24.0": No such file or directory (Connection: pgsql, Host: 127.0.0.1, Port: 5433, Database: gout-burn, SQL: CREATE OR REPLACE PROCEDURE public.add_columnstore_policy(IN hypertable regclass, IN after "any" DEFAULT NULL::unknown, IN if_not_exists boolean DEFAULT false, IN schedule_interval interval DEFAULT NULL::interval, IN initial_start timestamp with time zone DEFAULT NULL::timestamp with time zone, IN timezone text DEFAULT NULL::text, IN created_before interval DEFAULT NULL::interval)\n LANGUAGE c\nAS '$libdir/timescaledb-2.24.0', $$ts_policy_compression_add$$)\n📍 Location: /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php:831\n🌍 Env: local\n🧵 Trace (top 20):\n#0 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php(787): Illuminate\\Database\\Connection->runQueryCallback('CREATE OR REPLA...', Array, Object(Closure))\n#1 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php(620): Illuminate\\Database\\Connection->run('CREATE OR REPLA...', Array, Object(Closure))\n#2 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php(491): Illuminate\\Database\\Connection->unprepared('CREATE OR REPLA...')\n#3 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Support/Facades/Facade.php(363): Illuminate\\Database\\DatabaseManager->__call('unprepared', Array)\n#4 /Users/opts/projects/gout-burn/gb/database/migrations/2026_01_09_025021_create_add_columnstore_policy_proc.php(13): Illuminate\\Support\\Facades\\Facade::__callStatic('unprepared', Array)\n#5 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(517): Illuminate\\Database\\Migrations\\Migration@anonymous->up()\n#6 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(442): Illuminate\\Database\\Migrations\\Migrator->runMethod(Object(Illuminate\\Database\\PostgresConnection), Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#7 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Concerns/ManagesTransactions.php(35): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}(Object(Illuminate\\Database\\PostgresConnection))\n#8 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(450): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#9 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->runMigration(Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#10 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Console/View/Components/Task.php(41): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}()\n#11 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(812): Illuminate\\Console\\View\\Components\\Task->render('2026_01_09_0250...', Object(Closure))\n#12 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->write('Illuminate\\\\Cons...', '2026_01_09_0250...', Object(Closure))\n#13 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(211): Illuminate\\Database\\Migrations\\Migrator->runUp('/Users/opts/pro...', 2, false)\n#14 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(138): Illuminate\\Database\\Migrations\\Migrator->runPending(Array, Array)\n#15 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(116): Illuminate\\Database\\Migrations\\Migrator->run(Array, Array)\n#16 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(669): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->Illuminate\\Database\\Console\\Migrations\\{closure}()\n#17 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(109): Illuminate\\Database\\Migrations\\Migrator->usingConnection(NULL, Object(Closure))\n#18 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(88): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->runMigrations()\n#19 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->handle()	system_warning	system	0	\N	0	\N	\N	2026-02-05 14:39:49	2026-02-05 14:39:49	0
3	4	🚨 System Warning	🧩 Type: ErrorException\n💥 Message: Undefined variable $libdir\n📍 Location: /Users/opts/projects/gout-burn/gb/database/migrations/2026_01_09_025021_create_add_columnstore_policy_proc.php:20\n🌍 Env: local\n🧵 Trace (top 20):\n#0 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php(258): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, 'Undefined varia...', '/Users/opts/pro...', 20)\n#1 /Users/opts/projects/gout-burn/gb/database/migrations/2026_01_09_025021_create_add_columnstore_policy_proc.php(20): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, 'Undefined varia...', '/Users/opts/pro...', 20)\n#2 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(517): Illuminate\\Database\\Migrations\\Migration@anonymous->up()\n#3 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(442): Illuminate\\Database\\Migrations\\Migrator->runMethod(Object(Illuminate\\Database\\PostgresConnection), Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#4 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Concerns/ManagesTransactions.php(35): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}(Object(Illuminate\\Database\\PostgresConnection))\n#5 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(450): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#6 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->runMigration(Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#7 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Console/View/Components/Task.php(41): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}()\n#8 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(812): Illuminate\\Console\\View\\Components\\Task->render('2026_01_09_0250...', Object(Closure))\n#9 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->write('Illuminate\\\\Cons...', '2026_01_09_0250...', Object(Closure))\n#10 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(211): Illuminate\\Database\\Migrations\\Migrator->runUp('/Users/opts/pro...', 3, false)\n#11 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(138): Illuminate\\Database\\Migrations\\Migrator->runPending(Array, Array)\n#12 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(116): Illuminate\\Database\\Migrations\\Migrator->run(Array, Array)\n#13 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(669): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->Illuminate\\Database\\Console\\Migrations\\{closure}()\n#14 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(109): Illuminate\\Database\\Migrations\\Migrator->usingConnection(NULL, Object(Closure))\n#15 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(88): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->runMigrations()\n#16 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->handle()\n#17 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))	system_warning	system	0	\N	0	\N	\N	2026-02-05 14:50:03	2026-02-05 14:50:03	0
4	4	🚨 System Warning	🧩 Type: ParseError\n💥 Message: syntax error, unexpected single-quoted string "$libdir/timescaledb-%s"\n📍 Location: /Users/opts/projects/gout-burn/gb/database/migrations/2026_01_09_025021_create_add_columnstore_policy_proc.php:21\n🌍 Env: local\n🧵 Trace (top 20):\n#0 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php(149): Illuminate\\Filesystem\\Filesystem::Illuminate\\Filesystem\\{closure}()\n#1 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(598): Illuminate\\Filesystem\\Filesystem->requireOnce('/Users/opts/pro...')\n#2 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(131): Illuminate\\Database\\Migrations\\Migrator->requireFiles(Array)\n#3 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(116): Illuminate\\Database\\Migrations\\Migrator->run(Array, Array)\n#4 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(669): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->Illuminate\\Database\\Console\\Migrations\\{closure}()\n#5 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(109): Illuminate\\Database\\Migrations\\Migrator->usingConnection(NULL, Object(Closure))\n#6 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(88): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->runMigrations()\n#7 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->handle()\n#8 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#9 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#10 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#11 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#12 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Console/Command.php(211): Illuminate\\Container\\Container->call(Array)\n#13 /Users/opts/projects/gout-burn/gb/vendor/symfony/console/Command/Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#14 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Console/Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#15 /Users/opts/projects/gout-burn/gb/vendor/symfony/console/Application.php(1102): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#16 /Users/opts/projects/gout-burn/gb/vendor/symfony/console/Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Database\\Console\\Migrations\\MigrateCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#17 /Users/opts/projects/gout-burn/gb/vendor/symfony/console/Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#18 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#19 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))	system_warning	system	0	\N	0	\N	\N	2026-02-05 14:52:23	2026-02-05 14:52:23	0
5	4	🚨 System Warning	🧩 Type: Illuminate\\Database\\QueryException\n💥 Message: SQLSTATE[58P01]: Undefined file: 7 ERROR:  could not access file "$libdir/timescaledb-2.24.0": No such file or directory (Connection: pgsql, Host: 127.0.0.1, Port: 5433, Database: gout-burn, SQL: CREATE OR REPLACE PROCEDURE public.add_process_hypertable_invalidations_policy(IN hypertable regclass, IN schedule_interval interval, IN if_not_exists boolean DEFAULT false, IN initial_start timestamp with time zone DEFAULT NULL::timestamp with time zone, IN timezone text DEFAULT NULL::text)\n LANGUAGE c\nAS '$libdir/timescaledb-2.24.0', $$ts_policy_process_hyper_inval_add$$)\n📍 Location: /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php:831\n🌍 Env: local\n🧵 Trace (top 20):\n#0 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php(787): Illuminate\\Database\\Connection->runQueryCallback('CREATE OR REPLA...', Array, Object(Closure))\n#1 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Connection.php(620): Illuminate\\Database\\Connection->run('CREATE OR REPLA...', Array, Object(Closure))\n#2 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php(491): Illuminate\\Database\\Connection->unprepared('CREATE OR REPLA...')\n#3 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Support/Facades/Facade.php(363): Illuminate\\Database\\DatabaseManager->__call('unprepared', Array)\n#4 /Users/opts/projects/gout-burn/gb/database/migrations/2026_01_09_025021_create_add_process_hypertable_invalidations_policy_proc.php(13): Illuminate\\Support\\Facades\\Facade::__callStatic('unprepared', Array)\n#5 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(517): Illuminate\\Database\\Migrations\\Migration@anonymous->up()\n#6 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(442): Illuminate\\Database\\Migrations\\Migrator->runMethod(Object(Illuminate\\Database\\PostgresConnection), Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#7 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Concerns/ManagesTransactions.php(35): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}(Object(Illuminate\\Database\\PostgresConnection))\n#8 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(450): Illuminate\\Database\\Connection->transaction(Object(Closure))\n#9 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->runMigration(Object(Illuminate\\Database\\Migrations\\Migration@anonymous), 'up')\n#10 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Console/View/Components/Task.php(41): Illuminate\\Database\\Migrations\\Migrator->Illuminate\\Database\\Migrations\\{closure}()\n#11 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(812): Illuminate\\Console\\View\\Components\\Task->render('2026_01_09_0250...', Object(Closure))\n#12 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(253): Illuminate\\Database\\Migrations\\Migrator->write('Illuminate\\\\Cons...', '2026_01_09_0250...', Object(Closure))\n#13 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(211): Illuminate\\Database\\Migrations\\Migrator->runUp('/Users/opts/pro...', 3, false)\n#14 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(138): Illuminate\\Database\\Migrations\\Migrator->runPending(Array, Array)\n#15 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(116): Illuminate\\Database\\Migrations\\Migrator->run(Array, Array)\n#16 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Migrations/Migrator.php(669): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->Illuminate\\Database\\Console\\Migrations\\{closure}()\n#17 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(109): Illuminate\\Database\\Migrations\\Migrator->usingConnection(NULL, Object(Closure))\n#18 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Database/Console/Migrations/MigrateCommand.php(88): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->runMigrations()\n#19 /Users/opts/projects/gout-burn/gb/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Database\\Console\\Migrations\\MigrateCommand->handle()	system_warning	system	0	\N	0	\N	\N	2026-02-05 14:52:41	2026-02-05 14:52:41	0
\.


--
-- Data for Name: token_price; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.token_price (id, token_name, token_price, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: token_price_up_chain; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.token_price_up_chain (id, token_name, token_price, transaction_hash, status, created_at, updated_at, token_price_chain) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, address, p_id, path, remark, status, active) FROM stdin;
\.


--
-- Name: chunk_column_stats_id_seq; Type: SEQUENCE SET; Schema: _timescaledb_catalog; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_catalog.chunk_column_stats_id_seq', 1, false);


--
-- Name: chunk_constraint_name; Type: SEQUENCE SET; Schema: _timescaledb_catalog; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_catalog.chunk_constraint_name', 1, false);


--
-- Name: chunk_id_seq; Type: SEQUENCE SET; Schema: _timescaledb_catalog; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_catalog.chunk_id_seq', 1, false);


--
-- Name: continuous_agg_migrate_plan_step_step_id_seq; Type: SEQUENCE SET; Schema: _timescaledb_catalog; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_catalog.continuous_agg_migrate_plan_step_step_id_seq', 1, false);


--
-- Name: dimension_id_seq; Type: SEQUENCE SET; Schema: _timescaledb_catalog; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_catalog.dimension_id_seq', 1, false);


--
-- Name: dimension_slice_id_seq; Type: SEQUENCE SET; Schema: _timescaledb_catalog; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_catalog.dimension_slice_id_seq', 1, false);


--
-- Name: hypertable_id_seq; Type: SEQUENCE SET; Schema: _timescaledb_catalog; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_catalog.hypertable_id_seq', 1, false);


--
-- Name: bgw_job_id_seq; Type: SEQUENCE SET; Schema: _timescaledb_config; Owner: postgres
--

SELECT pg_catalog.setval('_timescaledb_config.bgw_job_id_seq', 1000, false);


--
-- Name: address_sign_code_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.address_sign_code_id_seq', 1, false);


--
-- Name: admin_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admin_users_id_seq', 1, false);


--
-- Name: blockchain_block_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_block_id_seq', 3, true);


--
-- Name: blockchain_check_newblock_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_check_newblock_id_seq', 1, false);


--
-- Name: blockchain_contract_event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_contract_event_id_seq', 4, true);


--
-- Name: blockchain_contract_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_contract_id_seq', 2, true);


--
-- Name: blockchain_contract_sender_wallets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_contract_sender_wallets_id_seq', 1, false);


--
-- Name: blockchain_event_error_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_event_error_id_seq', 1, false);


--
-- Name: blockchain_events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_events_id_seq', 1, false);


--
-- Name: blockchain_rpc_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_rpc_id_seq', 1, false);


--
-- Name: blockchain_transaction_queue_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blockchain_transaction_queue_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 160, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: system_setting_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.system_setting_id_seq', 1, true);


--
-- Name: tax_processor_dispatch_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tax_processor_dispatch_logs_id_seq', 1, false);


--
-- Name: telegram_bot_group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telegram_bot_group_id_seq', 1, false);


--
-- Name: telegram_bot_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telegram_bot_id_seq', 1, false);


--
-- Name: telegram_contribution_group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telegram_contribution_group_id_seq', 1, false);


--
-- Name: telegram_group_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telegram_group_messages_id_seq', 1, false);


--
-- Name: telegram_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telegram_messages_id_seq', 5, true);


--
-- Name: token_price_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.token_price_id_seq', 1, false);


--
-- Name: token_price_up_chain_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.token_price_up_chain_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, false);


--
-- Name: address_sign_code address_sign_code_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.address_sign_code
    ADD CONSTRAINT address_sign_code_pkey PRIMARY KEY (id);


--
-- Name: admin_users admin_users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_email_unique UNIQUE (email);


--
-- Name: admin_users admin_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_pkey PRIMARY KEY (id);


--
-- Name: admin_users admin_users_username_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_username_unique UNIQUE (username);


--
-- Name: blockchain_block blockchain_block_chain_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_block
    ADD CONSTRAINT blockchain_block_chain_unique UNIQUE (chain);


--
-- Name: blockchain_block blockchain_block_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_block
    ADD CONSTRAINT blockchain_block_pkey PRIMARY KEY (id);


--
-- Name: blockchain_check_newblock blockchain_check_newblock_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_check_newblock
    ADD CONSTRAINT blockchain_check_newblock_pkey PRIMARY KEY (id);


--
-- Name: blockchain_contract_event blockchain_contract_event_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_contract_event
    ADD CONSTRAINT blockchain_contract_event_pkey PRIMARY KEY (id);


--
-- Name: blockchain_contract blockchain_contract_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_contract
    ADD CONSTRAINT blockchain_contract_pkey PRIMARY KEY (id);


--
-- Name: blockchain_contract_sender_wallets blockchain_contract_sender_wallets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_contract_sender_wallets
    ADD CONSTRAINT blockchain_contract_sender_wallets_pkey PRIMARY KEY (id);


--
-- Name: blockchain_event_error blockchain_event_error_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_event_error
    ADD CONSTRAINT blockchain_event_error_pkey PRIMARY KEY (id);


--
-- Name: blockchain_events blockchain_events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_events
    ADD CONSTRAINT blockchain_events_pkey PRIMARY KEY (id);


--
-- Name: blockchain_rpc blockchain_rpc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_rpc
    ADD CONSTRAINT blockchain_rpc_pkey PRIMARY KEY (id);


--
-- Name: blockchain_transaction_queue blockchain_transaction_queue_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_transaction_queue
    ADD CONSTRAINT blockchain_transaction_queue_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: blockchain_events idx_event_unique_hash_log_name; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_events
    ADD CONSTRAINT idx_event_unique_hash_log_name UNIQUE (transaction_hash, log_index, event_name);


--
-- Name: blockchain_events idx_tx_log_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blockchain_events
    ADD CONSTRAINT idx_tx_log_unique UNIQUE (transaction_hash, log_index);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: system_setting system_setting_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_setting
    ADD CONSTRAINT system_setting_key_unique UNIQUE (key);


--
-- Name: system_setting system_setting_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_setting
    ADD CONSTRAINT system_setting_pkey PRIMARY KEY (id);


--
-- Name: tax_processor_dispatch_logs tax_processor_dispatch_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_processor_dispatch_logs
    ADD CONSTRAINT tax_processor_dispatch_logs_pkey PRIMARY KEY (id);


--
-- Name: tax_processor_dispatch_logs tax_processor_dispatch_logs_transaction_hash_log_index_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_processor_dispatch_logs
    ADD CONSTRAINT tax_processor_dispatch_logs_transaction_hash_log_index_unique UNIQUE (transaction_hash, log_index);


--
-- Name: telegram_bot_group telegram_bot_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_bot_group
    ADD CONSTRAINT telegram_bot_group_pkey PRIMARY KEY (id);


--
-- Name: telegram_bot telegram_bot_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_bot
    ADD CONSTRAINT telegram_bot_pkey PRIMARY KEY (id);


--
-- Name: telegram_contribution_group telegram_contribution_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_contribution_group
    ADD CONSTRAINT telegram_contribution_group_pkey PRIMARY KEY (id);


--
-- Name: telegram_group_messages telegram_group_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_group_messages
    ADD CONSTRAINT telegram_group_messages_pkey PRIMARY KEY (id);


--
-- Name: telegram_messages telegram_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telegram_messages
    ADD CONSTRAINT telegram_messages_pkey PRIMARY KEY (id);


--
-- Name: token_price token_price_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.token_price
    ADD CONSTRAINT token_price_pkey PRIMARY KEY (id);


--
-- Name: token_price_up_chain token_price_up_chain_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.token_price_up_chain
    ADD CONSTRAINT token_price_up_chain_pkey PRIMARY KEY (id);


--
-- Name: users users_address_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_address_unique UNIQUE (address);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: address_sign_code_address_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX address_sign_code_address_index ON public.address_sign_code USING btree (address);


--
-- Name: address_sign_code_code_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX address_sign_code_code_index ON public.address_sign_code USING btree (code);


--
-- Name: address_sign_code_expired_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX address_sign_code_expired_index ON public.address_sign_code USING btree (expired);


--
-- Name: blockchain_events_block_time_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX blockchain_events_block_time_index ON public.blockchain_events USING btree (block_time);


--
-- Name: blockchain_events_event_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX blockchain_events_event_name_index ON public.blockchain_events USING btree (event_name);


--
-- Name: blockchain_events_transaction_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX blockchain_events_transaction_hash_index ON public.blockchain_events USING btree (transaction_hash);


--
-- Name: blockchain_transaction_queue_address_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX blockchain_transaction_queue_address_index ON public.blockchain_transaction_queue USING btree (address);


--
-- Name: blockchain_transaction_queue_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX blockchain_transaction_queue_status_index ON public.blockchain_transaction_queue USING btree (status);


--
-- Name: blockchain_transaction_queue_transaction_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX blockchain_transaction_queue_transaction_hash_index ON public.blockchain_transaction_queue USING btree (transaction_hash);


--
-- Name: blockchain_transaction_queue_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX blockchain_transaction_queue_user_id_index ON public.blockchain_transaction_queue USING btree (user_id);


--
-- Name: contract_sender_wallets_wallet_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX contract_sender_wallets_wallet_name_index ON public.blockchain_contract_sender_wallets USING btree (wallet_name);


--
-- Name: idx_error_event_ref; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_error_event_ref ON public.blockchain_event_error USING btree (transaction_hash, log_index);


--
-- Name: idx_error_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_error_status ON public.blockchain_event_error USING btree (status);


--
-- Name: idx_users_path_gin_trgm; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_path_gin_trgm ON public.users USING gin (path public.gin_trgm_ops);


--
-- Name: idx_users_path_unique_hash; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX idx_users_path_unique_hash ON public.users USING btree (md5(path));


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: system_setting_category_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX system_setting_category_index ON public.system_setting USING btree (category);


--
-- Name: system_setting_is_public_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX system_setting_is_public_index ON public.system_setting USING btree (is_public);


--
-- Name: tax_processor_dispatch_logs_chain_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tax_processor_dispatch_logs_chain_id_index ON public.tax_processor_dispatch_logs USING btree (chain_id);


--
-- Name: token_price_token_name_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX token_price_token_name_created_at_index ON public.token_price USING btree (token_name, created_at);


--
-- Name: token_price_token_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX token_price_token_name_index ON public.token_price USING btree (token_name);


--
-- Name: token_price_up_chain_status_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX token_price_up_chain_status_created_at_index ON public.token_price_up_chain USING btree (status, created_at);


--
-- Name: token_price_up_chain_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX token_price_up_chain_status_index ON public.token_price_up_chain USING btree (status);


--
-- Name: token_price_up_chain_token_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX token_price_up_chain_token_name_index ON public.token_price_up_chain USING btree (token_name);


--
-- Name: token_price_up_chain_transaction_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX token_price_up_chain_transaction_hash_index ON public.token_price_up_chain USING btree (transaction_hash);


--
-- Name: users_p_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_p_id_index ON public.users USING btree (p_id);


--
-- PostgreSQL database dump complete
--

\unrestrict 4uLWlKuoSoKFCwaih82VBGuW0WxKIDPV72gNqgEppBLNCBtQoYAKuIwyuocXIk2

