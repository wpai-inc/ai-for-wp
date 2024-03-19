import {
  __experimentalHeading as Heading,
  Card,
  CardBody,
  CardHeader,
  __experimentalSpacer as Spacer,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from 'react';

declare const ajaxurl: string;

export const Logs = () => {
  const [errorLogs, setErrorLogs] = useState([]);

  useEffect(() => {
    fetch(ajaxurl + '?action=codewpai_logs')
      .then((response) => response.json())
      .then((data) => {
        setErrorLogs(data.data);
      });
  }, []);

  return (
    <>
      <Spacer marginBottom={6} />
      <Card>
        <CardHeader>
          <Heading>{__('Error Logs', 'codewpai')}</Heading>
        </CardHeader>
        <CardBody>
          {errorLogs && errorLogs.length > 0 ? (
            <ul>
              {errorLogs.map((log, index) => {
                return (
                  <li key={index} style={{ marginBottom: '10px' }}>
                    <div style={{ color: 'rgb(181, 0, 0)' }}>{log?.message}</div>
                    <div style={{ color: '#333' }}>
                      {log?.file_name}:{log?.line}
                    </div>
                  </li>
                );
              })}
            </ul>
          ) : (
            <p>{__('No error logs found', 'codewpai')}</p>
          )}
        </CardBody>
      </Card>

      <Spacer marginBottom={6} />
    </>
  );
};
