import express from 'express';
import cors from 'cors';
import { validateLicense } from './controllers/licenseController';

const app = express();
const PORT = process.env.PORT || 8080;

app.use(cors({
  origin: process.env.ALLOWED_ORIGINS ? process.env.ALLOWED_ORIGINS.split(',') : '*'
}));

app.use(express.json());

// Security Check & Licensing routes
app.post('/api/v1/license/validate', validateLicense);

// Health check status
app.get('/api/v1/health', (req, res) => {
  res.status(200).json({ status: 'OK', services: 'online' });
});

app.listen(PORT, () => {
  console.log(`Aprilo Platform Backend running on port ${PORT}`);
});
