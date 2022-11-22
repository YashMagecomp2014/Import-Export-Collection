import React, { useEffect, useState, useMemo } from "react"
import { GlobalAPIcall } from "../config/ApiUtils"
import CollectionPage from "./CollectionPage"
import { ProgressBar } from '@shopify/polaris';
import MaterialReactTable from 'material-react-table';
import CollectionList from "./CollectionList";
import RefreshIcon from '@mui/icons-material/Refresh';
import { IconButton, Tooltip } from '@mui/material';
import { Delete } from '@mui/icons-material';
import { Box } from '@mui/material';
import { Spinner } from '@shopify/polaris';

function HistoryList() {
  const [collections, setUsers] = useState([]);
  const [rowSelection, setRowSelection] = useState([]);
  const [progress, setProgress] = useState(true);

  
  const onclick = () => {
    fetchData();

  }

  const deleteHistory = async (e) => {
    setProgress(true);
    e.preventDefault();
    var selectedValue = rowSelection;
    const result = Object.keys(selectedValue);

    var data = new FormData();
    data.append("ids", result)

    var res = await GlobalAPIcall('POST', '/deleteimport', data);
    fetchData()
    setProgress(false);
    setUsers();

  }
  const fetchData = async () => {
    var res = await GlobalAPIcall('GET', '/import');
    setUsers(res)
    setProgress(false);
  }

  const columns = useMemo(
    () => [
      //column definitions...
      {
        accessorKey: 'created_at',
        header: 'Date and Time',
      },
      {
        accessorKey: 'type',
        header: 'Type',
      },
      {
        header: 'Download',
        accessorKey: 'path',
        Cell: ({ cell, row }) => {

          return <a className="btn btn-primary" href={row.original.path}>Download</a>
        }
      },
      {
        accessorKey: 'errors',
        header: 'Message',
        Cell: ({ cell, row }) => {
          var error = JSON.parse(row.original.errors);

          if(row.original.errors){
            return error.map(item => (
              <div key={item}>
                <p style={{ borderBottom: '1px solid black' }}>{item}</p>
              </div>
            ));
          }

        }

      },
      //end
    ],
    [],
  );
  useEffect(() => {
    fetchData()
  }, [])

  useEffect(() => {
    //do something when the row selection changes...
    console.info({ rowSelection });
  }, [rowSelection]);

  return (
    <>
      <div className="row">
        <div className="col-md-8">
          {progress && <Spinner accessibilityLabel="Spinner example" size="large" />}
          <MaterialReactTable
            columns={columns}
            data={collections ?? []}
            enableRowSelection={true}
            getRowId={(row) => row.id}
            onRowSelectionChange={setRowSelection}
            state={{ rowSelection }}
            enableGlobalFilter={false}
            enableColumnFilter={false}
            renderTopToolbarCustomActions={() => (
              <Box sx={{ display: 'flex', gap: '1rem' }}>
                <Tooltip arrow title="Refresh Data">
                  <IconButton onClick={onclick}>
                    <RefreshIcon />
                  </IconButton>
                </Tooltip>
                <Tooltip arrow title="deleteHistory">
                  <IconButton onClick={deleteHistory}>
                    <Delete style={{ color: 'red' }} />
                  </IconButton>
                </Tooltip>
              </Box>
            )}
            muiTableHeadCellProps={{
              //easier way to create media queries, no useMediaQuery hook needed.
              sx: {
                fontSize: {
                  xs: '14px',
                  sm: '15px',
                  md: '16px',
                  lg: '17px',
                  xl: '18px',
                },
              },
            }}
            muiTableBodyCellProps={{
              //easier way to create media queries, no useMediaQuery hook needed.
              sx: {
                fontSize: {
                  xs: '10px',
                  sm: '11px',
                  md: '12px',
                  lg: '13px',
                  xl: '14px',
                },
              },


            }}



          />
        </div>

        <CollectionPage fetchData={fetchData} />

      </div>
    </>
  );
}

export default HistoryList;