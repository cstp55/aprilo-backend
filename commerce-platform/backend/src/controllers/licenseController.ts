import { Request, Response } from 'express';
import { Pool } from 'pg';

// Setup database pool connection
const db = new Pool({
  connectionString: process.env.DATABASE_URL
});

export const validateLicense = async (req: Request, res: Response) => {
  const { license_key, store_domain } = req.body;

  if (!license_key || !store_domain) {
    return res.status(400).json({
      success: false,
      message: 'License key and store domain are required.'
    });
  }

  try {
    // Look up license key in database
    const result = await db.query(
      'SELECT id, tenant_id, allowed_domain, status FROM licenses WHERE license_key = $1',
      [license_key]
    );

    if (result.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Invalid license key. Purchase a valid license to connect.'
      });
    }

    const license = result.rows[0];

    // Case 1: License key is inactive (First time activation)
    if (license.status === 'inactive') {
      // Activate license and lock to store domain
      await db.query(
        'UPDATE licenses SET status = \'active\', allowed_domain = $1, activated_at = CURRENT_TIMESTAMP WHERE id = $2',
        [store_domain, license.id]
      );
      
      return res.status(200).json({
        success: true,
        message: 'License activated and locked to store domain successfully.',
        status: 'active'
      });
    }

    // Case 2: License key is already active
    if (license.status === 'active') {
      // Enforce anti-copy check: domain must match allowed_domain exactly
      if (license.allowed_domain !== store_domain) {
        return res.status(403).json({
          success: false,
          message: `License key mismatch. This key is already registered to another store domain: ${license.allowed_domain}`
        });
      }

      return res.status(200).json({
        success: true,
        message: 'License validated successfully.',
        status: 'active'
      });
    }

    // Case 3: License suspended or expired
    return res.status(403).json({
      success: false,
      message: `License state is ${license.status}. Please contact billing support.`
    });

  } catch (error) {
    console.error('Database query error on license check:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server validation error.'
    });
  }
};
